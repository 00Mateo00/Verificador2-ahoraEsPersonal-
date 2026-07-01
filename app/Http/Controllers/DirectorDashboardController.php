<?php

namespace App\Http\Controllers;

use App\Mail\NuevasActividadesPendientes;
use App\Models\Actividad;
use App\Models\Region;
use App\Models\Scopes\StatisticalYearScope;
use App\Models\Unidad;
use App\Services\MailService;
use Illuminate\Support\Facades\Auth;

class DirectorDashboardController extends Controller
{
    /**
     * Renderiza el Dashboard del Director Regional con estadísticas territoriales de su jurisdicción.
     */
    public function index()
    {
        // Control dinámico de vistas y filtros temporales para el Director
        $view = request('view', 'mes'); // 'mes', 'ano' o 'global'

        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');

        $selectedMonth = (int) request('mes', $currentMonth);
        $selectedYear = (int) request('ano', $currentYear);

        $user = Auth::user();
        $region = Region::where('user_id', $user->id)->first();

        // 1. Estadísticas operacionales restringidas de acuerdo a la vista y selectores usando el scope unificado
        $queryCargadas = Actividad::query()->where('estado', 'CARGADA')->forUser($user);
        $queryVerificadas = Actividad::query()->where('estado', 'VERIFICADA')->forUser($user);

        if ($view === 'global') {
            $queryCargadas->withoutGlobalScope(StatisticalYearScope::class);
            $queryVerificadas->withoutGlobalScope(StatisticalYearScope::class);
        } elseif ($selectedYear !== $currentYear) {
            $queryCargadas->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
            $queryVerificadas->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
        }

        if ($view === 'mes') {
            $queryCargadas->where('MES', $selectedMonth);
            $queryVerificadas->where('MES', $selectedMonth);
        }

        $totalCargadas = $queryCargadas->count();
        $totalVerificadas = $queryVerificadas->count();
        $totalActividades = $totalCargadas + $totalVerificadas;
        $porcentajeVerificacion = $totalActividades > 0 ? round(($totalVerificadas / $totalActividades) * 100, 1) : 0;

        // 2. Catálogo de unidades asignadas ordenadas por menor avance a mayor avance (Excluyendo unidades sin actividades)
        $unidadesEstadisticas = Unidad::query()
            ->with(['user'])
            ->where('region_id', $region?->id)
            ->get()
            ->map(function ($unidad) use ($selectedYear, $selectedMonth, $view, $currentYear) {
                $cargadasQuery = Actividad::where('unidad_id_asignada', $unidad->id)->where('estado', 'CARGADA');
                $verificadasQuery = Actividad::where('unidad_id_asignada', $unidad->id)->where('estado', 'VERIFICADA');

                if ($view === 'global') {
                    $cargadasQuery->withoutGlobalScope(StatisticalYearScope::class);
                    $verificadasQuery->withoutGlobalScope(StatisticalYearScope::class);
                } elseif ($selectedYear !== $currentYear) {
                    $cargadasQuery->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
                    $verificadasQuery->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
                }

                if ($view === 'mes') {
                    $cargadasQuery->where('MES', $selectedMonth);
                    $verificadasQuery->where('MES', $selectedMonth);
                }

                $cargadas = $cargadasQuery->count();
                $verificadas = $verificadasQuery->count();
                $total = $cargadas + $verificadas;

                if ($total === 0) {
                    return null;
                }

                $avance = $verificadas === 0 ? 0 : round(($verificadas / $total) * 100, 1);

                // Recuperar únicamente las actividades verificadas con archivos asociados que corresponden al periodo actual
                $actividadesVerificadasQuery = Actividad::with(['archivos'])
                    ->where('unidad_id_asignada', $unidad->id)
                    ->where('estado', 'VERIFICADA');

                if ($view === 'global') {
                    $actividadesVerificadasQuery->withoutGlobalScope(StatisticalYearScope::class);
                } elseif ($selectedYear !== $currentYear) {
                    $actividadesVerificadasQuery->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
                }

                if ($view === 'mes') {
                    $actividadesVerificadasQuery->where('MES', $selectedMonth);
                }

                $actividadesVerificadas = $actividadesVerificadasQuery->orderBy('FECHA', 'desc')->get();

                return [
                    'id' => $unidad->id,
                    'nombre' => $unidad->user->name ?? 'Unidad sin nombre',
                    'email' => $unidad->user->email ?? '',
                    'cargadas' => $cargadas,
                    'verificadas' => $verificadas,
                    'total' => $total,
                    'avance' => $avance,
                    'actividades_verificadas' => $actividadesVerificadas,
                ];
            })
            ->filter() // Filtrar nulos (unidades con 0 actividades totales en el periodo)
            ->sortBy('avance')
            ->values();

        // 3. Lista de actividades vigentes de sus unidades para el periodo seleccionado usando el scope unificado
        $queryActividades = Actividad::with(['archivos', 'unidadAsignada']);

        if ($view === 'global') {
            $queryActividades->withoutGlobalScope(StatisticalYearScope::class);
        } elseif ($selectedYear !== $currentYear) {
            $queryActividades->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
        }

        $queryActividades->where('activo', true)
            ->forUser($user);

        if ($view === 'mes') {
            $queryActividades->where('MES', $selectedMonth);
        }

        $actividades = $queryActividades->orderBy('FECHA', 'desc')
            ->paginate(15);

        return view('director.dashboard', compact(
            'region',
            'totalCargadas',
            'totalVerificadas',
            'totalActividades',
            'porcentajeVerificacion',
            'unidadesEstadisticas',
            'actividades',
            'view',
            'currentMonth',
            'currentYear',
            'selectedMonth',
            'selectedYear'
        ));
    }

    /**
     * Procesa la renotificación de una unidad regional validando aislamiento territorial.
     */
    public function renotificarUnidad(Unidad $unidad)
    {
        $region = Region::where('user_id', Auth::id())->first();
        if (! $region || $unidad->region_id !== $region->id) {
            abort(403, 'No tiene permisos para renotificar unidades fuera de su jurisdicción.');
        }

        // Control de concurrencia
        if ($unidad->ultima_notificacion_at?->isToday()) {
            return back()->with('success', "La unidad '{$unidad->user->name}' ya ha sido notificada hoy. El listado ha sido actualizado.");
        }

        $sent = MailService::sendSafe(
            $unidad->user->email,
            new NuevasActividadesPendientes($unidad),
            ['unidad_id' => $unidad->id]
        );

        if ($sent) {
            $unidad->ultima_notificacion_at = now();
            $unidad->save();

            return back()->with('success', "Se ha enviado una nueva renotificación de forma síncrona a la unidad '{$unidad->user->name}'.");
        }

        return back()->with('error', "El envío síncrono falló. Se ha archivado la renotificación en 'Correos Fallidos' para posterior gestión administrativa.");
    }
}

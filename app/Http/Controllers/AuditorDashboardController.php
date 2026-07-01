<?php

namespace App\Http\Controllers;

use App\Mail\NuevasActividadesPendientes;
use App\Models\Actividad;
use App\Models\CargaExcel;
use App\Models\Region;
use App\Models\Scopes\StatisticalYearScope;
use App\Models\Unidad;
use App\Services\MailService;

class AuditorDashboardController extends Controller
{
    /**
     * Renderiza el Dashboard del Auditor con estadísticas de solo lectura.
     */
    public function __invoke()
    {
        // Control dinámico de vistas y filtros temporales
        $view = request('view', 'mes'); // 'mes', 'ano' o 'global'

        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');

        // Cargar mes y año seleccionados con autodetección por defecto del periodo actual
        $selectedMonth = (int) request('mes', $currentMonth);
        $selectedYear = (int) request('ano', $currentYear);

        // 1. Métricas operacionales filtradas de acuerdo a la vista y selectores
        $queryCargadas = Actividad::query()->where('estado', 'CARGADA');
        $queryVerificadas = Actividad::query()->where('estado', 'VERIFICADA');

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

        $totalPlanillas = $view === 'global'
            ? CargaExcel::count()
            : CargaExcel::whereYear('created_at', $selectedYear)->count();

        // 2. Estadísticas territoriales consolidadas por región (Eager Loading para prevenir N+1)
        $regionesEstadisticas = Region::query()
            ->with(['user', 'unidades' => function ($query) use ($selectedYear, $selectedMonth, $view, $currentYear) {
                $query->withCount([
                    'actividadesAsignadas as cargadas_count' => function ($q) use ($selectedYear, $selectedMonth, $view, $currentYear) {
                        $q->where('estado', 'CARGADA');

                        if ($view === 'global') {
                            $q->withoutGlobalScope(StatisticalYearScope::class);
                        } elseif ($selectedYear !== $currentYear) {
                            $q->withoutGlobalScope(StatisticalYearScope::class)
                                ->where('AÑO', $selectedYear);
                        }

                        if ($view === 'mes') {
                            $q->where('MES', $selectedMonth);
                        }
                    },

                    'actividadesAsignadas as verificadas_count' => function ($q) use ($selectedYear, $selectedMonth, $view, $currentYear) {
                        $q->where('estado', 'VERIFICADA');

                        if ($view === 'global') {
                            $q->withoutGlobalScope(StatisticalYearScope::class);
                        } elseif ($selectedYear !== $currentYear) {
                            $q->withoutGlobalScope(StatisticalYearScope::class)
                                ->where('AÑO', $selectedYear);
                        }

                        if ($view === 'mes') {
                            $q->where('MES', $selectedMonth);
                        }
                    },
                ]);
            }])
            ->get()
            ->map(function ($region) {
                $cargadas = $region->unidades->sum('cargadas_count');
                $verificadas = $region->unidades->sum('verificadas_count');
                $total = $cargadas + $verificadas;

                return [
                    'nombre' => $region->region_nombre,
                    'director' => $region->user->name ?? 'Sin director',
                    'unidades_count' => $region->unidades->count(),
                    'cargadas' => $cargadas,
                    'verificadas' => $verificadas,
                    'total' => $total,
                    'avance' => $total > 0 ? round(($verificadas / $total) * 100, 1) : 0,
                ];
            });

        // 3. Unidades con actividades pendientes para el reenvío de notificaciones
        $unidadesPendientes = collect();

        if ($view !== 'global') {
            $unidadesPendientes = Unidad::query()
                ->with(['user', 'region'])
                ->whereHas('actividadesAsignadas', function ($q) use ($selectedYear, $selectedMonth, $view) {
                    $q->where('estado', 'CARGADA')
                        ->where('AÑO', $selectedYear)
                        ->when($view === 'mes', function ($subQ) use ($selectedMonth) {
                            $subQ->where('MES', $selectedMonth);
                        });
                })
                ->get();
        }

        // Últimas planillas importadas en el sistema
        $cargasRecientes = CargaExcel::query()
            ->with('usuario')
            ->latest()
            ->take(5)
            ->get();

        return view('auditor.dashboard', compact(
            'totalCargadas',
            'totalVerificadas',
            'totalActividades',
            'porcentajeVerificacion',
            'totalPlanillas',
            'regionesEstadisticas',
            'unidadesPendientes',
            'cargasRecientes',
            'view',
            'currentMonth',
            'currentYear',
            'selectedMonth',
            'selectedYear'
        ));
    }

    /**
     * Procesa la renotificación de una unidad desde el perfil de Auditor con acceso global.
     */
    public function renotificarUnidad(Unidad $unidad)
    {
        $sent = MailService::sendSafe(
            $unidad->user->email,
            new NuevasActividadesPendientes($unidad),
            ['unidad_id' => $unidad->id]
        );

        if ($sent) {
            // Actualización atómica de la fecha sin disparar updated_at inexistente
            $unidad->ultima_notificacion_at = now();
            $unidad->save();

            return back()->with('success', "Se ha enviado una nueva renotificación de forma síncrona a la unidad '{$unidad->user->name}'.");
        }

        return back()->with('error', "El envío síncrono falló. Se ha archivado la renotificación en 'Correos Fallidos'.");
    }
}

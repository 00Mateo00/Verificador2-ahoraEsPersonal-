<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\CargaExcel;
use App\Models\Region;
use App\Models\Scopes\StatisticalYearScope;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Cockpit Unificado: Compila dinámicamente métricas y widgets según permisos del usuario.
     * Filtrado únicamente por el Año Estadístico seleccionado (por defecto es el año más reciente).
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        // Determinar el Año Estadístico activo por defecto (Año más reciente)
        $activeYear = Cache::remember('active_statistical_year_cache', 300, function () {
            return Actividad::max('AÑO') ?: (int) date('Y');
        });

        $selectedYear = (int) $request->query('ano', $activeYear);

        $data = compact('selectedYear', 'activeYear');

        // 1. Compilar datos para widgets de Supervisión Global (Admin o Auditor)
        if ($user->hasPermissionTo('historial.ver-global') || $user->hasPermissionTo('usuarios.crear')) {
            $queryCargadas = Actividad::query()->where('estado', 'CARGADA');
            $queryVerificadas = Actividad::query()->where('estado', 'VERIFICADA');

            // Filtro dinámico si es distinto al año actual predeterminado
            if ($selectedYear !== $activeYear) {
                $queryCargadas->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
                $queryVerificadas->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
            }

            $data['totalCargadas'] = $queryCargadas->count();
            $data['totalVerificadas'] = $queryVerificadas->count();
            $data['totalActividades'] = $data['totalCargadas'] + $data['totalVerificadas'];
            $data['porcentajeVerificacion'] = $data['totalActividades'] > 0 ? round(($data['totalVerificadas'] / $data['totalActividades']) * 100, 1) : 0;

            $data['totalPlanillas'] = CargaExcel::whereYear('created_at', $selectedYear)->count();

            // Eager loading de regiones y unidades expandibles para auditoría
            $data['regionesEstadisticas'] = Region::query()
                ->with(['user', 'unidades.user'])
                ->get()
                ->map(function ($region) use ($selectedYear, $activeYear) {
                    $unidadesMapeadas = $region->unidades->map(function ($unidad) use ($selectedYear, $activeYear) {
                        $cargadasQuery = Actividad::where('unidad_id_asignada', $unidad->id)->where('estado', 'CARGADA');
                        $verificadasQuery = Actividad::where('unidad_id_asignada', $unidad->id)->where('estado', 'VERIFICADA');

                        if ($selectedYear !== $activeYear) {
                            $cargadasQuery->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
                            $verificadasQuery->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
                        }

                        $cargadas = $cargadasQuery->count();
                        $verificadas = $verificadasQuery->count();
                        $total = $cargadas + $verificadas;

                        $avance = $total === 0 ? 0 : round(($verificadas / $total) * 100, 1);

                        return [
                            'id' => $unidad->id,
                            'nombre' => $unidad->user->name ?? 'Unidad sin nombre',
                            'email' => $unidad->user->email ?? '',
                            'cargadas' => $cargadas,
                            'verificadas' => $verificadas,
                            'total' => $total,
                            'avance' => $avance,
                            'status' => $total === 0 ? 'sin_actividades' : ($cargadas > 0 ? 'pendientes' : 'al_dia'),
                            'notificada_hoy' => $unidad->ultima_notificacion_at?->isToday() ?? false,
                        ];
                    });

                    $cargadas = $unidadesMapeadas->sum('cargadas');
                    $verificadas = $unidadesMapeadas->sum('verificadas');
                    $total = $cargadas + $verificadas;

                    return [
                        'id' => $region->id,
                        'nombre' => $region->region_nombre,
                        'director' => $region->user->name ?? 'Sin director',
                        'unidades_count' => $region->unidades->count(),
                        'cargadas' => $cargadas,
                        'verificadas' => $verificadas,
                        'total' => $total,
                        'avance' => $total > 0 ? round(($verificadas / $total) * 100, 1) : 0,
                        'unidades' => $unidadesMapeadas->sortBy('avance')->values()->toArray(),
                    ];
                });

            $data['cargasRecientes'] = CargaExcel::query()
                ->with('usuario')
                ->latest()
                ->take(5)
                ->get();
        }

        // 2. Compilar datos para widgets de Supervisión Territorial (Director Regional)
        if ($user->hasPermissionTo('historial.ver-regional') && ! $user->hasPermissionTo('historial.ver-global')) {
            $region = Region::where('user_id', $user->id)->first();
            $data['region'] = $region;

            $queryCargadas = Actividad::query()->where('estado', 'CARGADA')->forUser($user);
            $queryVerificadas = Actividad::query()->where('estado', 'VERIFICADA')->forUser($user);

            if ($selectedYear !== $activeYear) {
                $queryCargadas->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
                $queryVerificadas->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
            }

            $data['totalCargadas'] = $queryCargadas->count();
            $data['totalVerificadas'] = $queryVerificadas->count();
            $data['totalActividades'] = $data['totalCargadas'] + $data['totalVerificadas'];
            $data['porcentajeVerificacion'] = $data['totalActividades'] > 0 ? round(($data['totalVerificadas'] / $data['totalActividades']) * 100, 1) : 0;

            $data['unidadesEstadisticas'] = Unidad::query()
                ->with(['user'])
                ->where('region_id', $region?->id)
                ->get()
                ->map(function ($unidad) use ($selectedYear, $activeYear) {
                    $cargadasQuery = Actividad::where('unidad_id_asignada', $unidad->id)->where('estado', 'CARGADA');
                    $verificadasQuery = Actividad::where('unidad_id_asignada', $unidad->id)->where('estado', 'VERIFICADA');

                    if ($selectedYear !== $activeYear) {
                        $cargadasQuery->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
                        $verificadasQuery->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $selectedYear);
                    }

                    $cargadas = $cargadasQuery->count();
                    $verificadas = $verificadasQuery->count();
                    $total = $cargadas + $verificadas;

                    $avance = $total === 0 ? 0 : round(($verificadas / $total) * 100, 1);

                    return [
                        'id' => $unidad->id,
                        'nombre' => $unidad->user->name ?? 'Unidad sin nombre',
                        'email' => $unidad->user->email ?? '',
                        'cargadas' => $cargadas,
                        'verificadas' => $verificadas,
                        'total' => $total,
                        'avance' => $avance,
                        'status' => $total === 0 ? 'sin_actividades' : ($cargadas > 0 ? 'pendientes' : 'al_dia'),
                        'notificada_hoy' => $unidad->ultima_notificacion_at?->isToday() ?? false,
                    ];
                })
                ->sortBy('avance')
                ->values();
        }

        return view('dashboard', $data);
    }
}

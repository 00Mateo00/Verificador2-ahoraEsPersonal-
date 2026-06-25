<?php

namespace App\Http\Controllers;

use App\Mail\NuevasActividadesPendientes;
use App\Models\Actividad;
use App\Models\CargaExcel;
use App\Models\Region;
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
        $queryCargadas = Actividad::where('estado', 'CARGADA');
        $queryVerificadas = Actividad::where('estado', 'VERIFICADA');

        if ($view !== 'global') {
            $queryCargadas->where('AÑO', $selectedYear);
            $queryVerificadas->where('AÑO', $selectedYear);

            if ($view === 'mes') {
                $queryCargadas->where('MES', $selectedMonth);
                $queryVerificadas->where('MES', $selectedMonth);
            }
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
            ->with(['user', 'unidades' => function ($query) use ($selectedYear, $selectedMonth, $view) {
                $query->withCount([
                    'actividadesAsignadas as cargadas_count' => function ($q) use ($selectedYear, $selectedMonth, $view) {
                        $q->where('estado', 'CARGADA')
                            ->when($view !== 'global', function ($subQ) use ($selectedYear) {
                                $subQ->where('AÑO', $selectedYear);
                            })
                            ->when($view === 'mes', function ($subQ) use ($selectedMonth) {
                                $subQ->where('MES', $selectedMonth);
                            });
                    },
                    'actividadesAsignadas' => function ($query) use ($selectedYear, $selectedMonth, $view) {
                        $query->where('estado', 'VERIFICADA');
                        if ($view === 'mes') {
                            $query->where('MES', $selectedMonth)->where('AÑO', $selectedYear);
                        } elseif ($view === 'ano') {
                            $query->where('AÑO', $selectedYear);
                        }
                    },
                ]);
            }])
            ->get()
            ->map(function ($region) {
                $cargadas = $region->unidades->sum('cargadas_count');
                $verificadas = $region->unidades->sum('actividades_asignadas_count');
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

        // 3. Unidades con actividades pendientes para el reenvío de notificaciones (Solo en vistas mes/ano)
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
            return back()->with('success', "Se ha enviado una nueva renotificación de forma síncrona a la unidad '{$unidad->user->name}'.");
        }

        return back()->with('error', "El envío síncrono falló. Se ha archivado la renotificación en 'Correos Fallidos'.");
    }
}

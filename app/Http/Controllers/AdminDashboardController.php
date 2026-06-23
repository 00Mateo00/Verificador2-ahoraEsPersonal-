<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\CargaExcel;
use App\Models\Region;

class AdminDashboardController extends Controller
{
    /**
     * Renderiza el Dashboard del Administrador con KPIs generales y del lote de Excel.
     */
    public function __invoke()
    {
        // Métricas operacionales consolidadas
        $totalCargadas = Actividad::where('estado', 'CARGADA')->count();
        $totalVerificadas = Actividad::where('estado', 'VERIFICADA')->count();
        $totalActividades = $totalCargadas + $totalVerificadas;
        $porcentajeVerificacion = $totalActividades > 0 ? round(($totalVerificadas / $totalActividades) * 100, 1) : 0;
        $totalPlanillas = CargaExcel::count();

        // Estadísticas territoriales consolidadas por región (Eager Loading para prevenir N+1)
        $regionesEstadisticas = Region::query()
            ->with(['user', 'unidades' => function ($query) {
                $query->withCount([
                    'actividadesAsignadas as cargadas_count' => function ($q) {
                        $q->where('estado', 'CARGADA');
                    },
                    'actividadesAsignadas as verificadas_count' => function ($q) {
                        $q->where('estado', 'VERIFICADA');
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

        // Últimas planillas importadas en el sistema
        $cargasRecientes = CargaExcel::query()
            ->with('usuario')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalCargadas',
            'totalVerificadas',
            'totalActividades',
            'porcentajeVerificacion',
            'totalPlanillas',
            'regionesEstadisticas',
            'cargasRecientes'
        ));
    }
}
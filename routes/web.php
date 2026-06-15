<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\DescargaVerificadorController;
use App\Models\Actividad;
use App\Models\CargaExcel;
use App\Models\Region;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $rol = Auth::user()->rol;
        info("(routing info): Usuario autenticado con rol: $rol");
        if ($rol === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($rol === 'cargador') {
            return redirect()->route('actividades.importar');
        }
        if ($rol === 'unidad') {
            return redirect()->route('unidad.dashboard');
        }

        return redirect()->route('actividades.historial');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Descarga segura de archivos verificadores (Almacenamiento Privado)
    Route::get('/archivos/{archivo}/descargar', [DescargaVerificadorController::class, 'descargar'])
        ->name('archivos.descargar');

    // Historial global: Accesible por todos los roles autenticados (Renombrado de Consulta a Historial)
    Route::get('/historial', [ActividadController::class, 'historial'])
        ->middleware('role:admin,director,auditor,cargador,unidad')
        ->name('actividades.historial');

    // / Rutas exclusivas de Administración
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
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
        })->name('admin.dashboard');

        Route::get('/admin/actividades', [ActividadController::class, 'index'])->name('admin.actividades');

        // Vista de Unidades en el menú lateral: Listado de usuarios del sistema
        Route::get('/admin/unidades', function () {
            $search = request('search');
            $usuarios = User::query()
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderBy('rol', 'asc')
                ->orderBy('name', 'asc')
                ->paginate(15);

            return view('admin.edicion', compact('usuarios', 'search'));
        })->name('admin.unidades');

        Route::get('/admin/edicion', function () {
            session(['modo_edicion' => true]);

            return redirect()->route('actividades.historial')->with('success', 'Modo edición activado. Ahora puede administrar verificadores directamente en las tarjetas de actividad.');
        })->middleware('password.confirm')->name('admin.edicion');

        // Salir del modo edición administrativa
        Route::get('/admin/salir-edicion', function () {
            session()->forget('modo_edicion');

            return redirect()->route('actividades.historial')->with('success', 'Modo edición desactivado.');
        })->name('admin.salir-edicion');

        // Acción crítica: Alternar estado de cuentas de usuario. Requiere que el modo_edicion esté activo en sesión.
        Route::patch('/admin/usuarios/{user}/toggle', function (User $user) {
            // Defensa: Bloquear si no se encuentra en modo edición
            if (! session('modo_edicion')) {
                abort(403, 'Acción bloqueada. Debe activar el Modo Edición para realizar modificaciones en las unidades.');
            }

            if ($user->id === auth()->id()) {
                return back()->with('error', 'No puede deshabilitar su propia cuenta de administrador.');
            }

            $user->update([
                'estado' => ! $user->estado,
            ]);

            $statusText = $user->estado ? 'habilitada' : 'deshabilitada';

            return back()->with('success', "La cuenta de {$user->name} ha sido {$statusText} con éxito.");
        })->name('admin.usuarios.toggle');
    });

    // Rutas exclusivas de Carga Masiva (Excel)
    Route::middleware(['role:admin,cargador'])->group(function () {
        Route::get('/actividades/importar', function () {
            return view('actividades.import');
        })->name('actividades.importar');
    });

    // Rutas exclusivas de Unidades Operativas
    Route::middleware(['role:unidad'])->group(function () {
        Route::get('/unidad/dashboard', function () {
            return view('unidad.dashboard');
        })->name('unidad.dashboard');
    });
});
require __DIR__.'/settings.php';

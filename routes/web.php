<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\DescargaVerificadorController;
use App\Mail\NuevasActividadesPendientes;
use App\Models\Actividad;
use App\Models\CargaExcel;
use App\Models\Region;
use App\Models\Unidad;
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
        if ($rol === 'auditor') {
            return redirect()->route('auditor.dashboard');
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

    // Rutas exclusivas del Auditor (Dashboard con estadísticas de solo lectura)
    Route::middleware(['role:auditor'])->group(function () {
        Route::get('/auditor/dashboard', function () {
            // Control dinámico de vistas y filtros temporales para el Auditor
            $view = request('view', 'mes'); // 'mes', 'año' o 'global'

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
                        'actividadesAsignadas as verificadas_count' => function ($q) use ($selectedYear, $selectedMonth, $view) {
                            $q->where('estado', 'VERIFICADA')
                                ->when($view !== 'global', function ($subQ) use ($selectedYear) {
                                    $subQ->where('AÑO', $selectedYear);
                                })
                                ->when($view === 'mes', function ($subQ) use ($selectedMonth) {
                                    $subQ->where('MES', $selectedMonth);
                                });
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
        })->name('auditor.dashboard');

        // Acción síncrona/en colas de renotificación para el Auditor
        Route::post('/auditor/unidades/{unidad}/renotificar', function (Unidad $unidad) {
            if (auth()->user()->rol !== 'auditor') {
                abort(403, 'Solo el rol de auditor puede despachar renotificaciones.');
            }

            // Despachar la renotificación agrupada asíncronamente en la cola
            Mail::to($unidad->user->email)->queue(new NuevasActividadesPendientes($unidad));

            return back()->with('success', "Se ha enviado una nueva renotificación por correo a la cola de procesamiento para la unidad '{$unidad->user->name}'.");
        })->name('auditor.unidades.renotificar');
    });

    // Rutas exclusivas del Director Regional
    Route::middleware(['role:director'])->group(function () {
        Route::get('/director/dashboard', function () {
            $currentMonth = (int) date('m');
            $currentYear = (int) date('Y');

            // Encontrar la región del director autenticado
            $region = Region::where('user_id', Auth::id())->first();
            $unidadIds = $region ? $region->unidades->pluck('id')->toArray() : [];

            // Estadísticas del mes estadístico actual restringidas a las unidades de su región
            $totalCargadas = Actividad::where('estado', 'CARGADA')
                ->whereIn('unidad_id_asignada', $unidadIds)
                ->where('MES', $currentMonth)
                ->where('AÑO', $currentYear)
                ->count();

            $totalVerificadas = Actividad::where('estado', 'VERIFICADA')
                ->whereIn('unidad_id_asignada', $unidadIds)
                ->where('MES', $currentMonth)
                ->where('AÑO', $currentYear)
                ->count();

            $totalActividades = $totalCargadas + $totalVerificadas;
            $porcentajeVerificacion = $totalActividades > 0 ? round(($totalVerificadas / $totalActividades) * 100, 1) : 0;

            // Lista de actividades vigentes de sus unidades para el mes actual
            $actividades = Actividad::with(['archivos', 'unidadAsignada'])
                ->whereIn('unidad_id_asignada', $unidadIds)
                ->where('MES', $currentMonth)
                ->where('AÑO', $currentYear)
                ->orderBy('FECHA', 'desc')
                ->paginate(15);

            // Unidades con firmas pendientes en su región únicamente
            $unidadesPendientes = Unidad::query()
                ->with(['user'])
                ->where('region_id', $region?->id)
                ->whereHas('actividadesAsignadas', function ($q) use ($currentMonth, $currentYear) {
                    $q->where('estado', 'CARGADA')
                        ->where('MES', $currentMonth)
                        ->where('AÑO', $currentYear);
                })
                ->get();

            return view('director.dashboard', compact(
                'region',
                'totalCargadas',
                'totalVerificadas',
                'totalActividades',
                'porcentajeVerificacion',
                'actividades',
                'unidadesPendientes',
                'currentMonth',
                'currentYear'
            ));
        })->name('director.dashboard');

        // Acción de renotificación regional asíncrona
        Route::post('/director/unidades/{unidad}/renotificar', function (Unidad $unidad) {
            $region = Region::where('user_id', Auth::id())->first();
            if (! $region || $unidad->region_id !== $region->id) {
                abort(403, 'No tiene permisos para renotificar unidades fuera de su jurisdicción.');
            }

            Mail::to($unidad->user->email)->queue(new NuevasActividadesPendientes($unidad));

            return back()->with('success', "Se ha enviado una nueva renotificación de forma asíncrona a la unidad '{$unidad->user->name}'.");
        })->name('director.unidades.renotificar');
    });

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

        Route::get('/admin/actividades', [ActividadController::class, 'historial'])->name('admin.actividades');

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
            session([
                'modo_edicion' => true,
                'modo_edicion_last_activity' => time(),
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Modo edición activado. Las opciones de edición crítica ahora son usables.');
        })->middleware('password.confirm')->name('admin.edicion');

        // Salir del modo edición administrativa, invalidar confirmación de password de Laravel y retornar al dashboard
        Route::get('/admin/salir-edicion', function () {
            session()->forget([
                'modo_edicion',
                'modo_edicion_last_activity',
                'auth.password_confirmed_at', // Fuerza la reconfirmación de contraseña al volver a entrar
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Modo edición desactivado. Ha retornado al modo de visualización segura.');
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

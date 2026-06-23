<?php

use App\Enums\UserRole;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\DescargaVerificadorController;
use App\Mail\NuevasActividadesPendientes;
use App\Mail\PasswordRenewalMail;
use App\Models\Actividad;
use App\Models\CargaExcel;
use App\Models\Region;
use App\Models\Unidad;
use App\Models\User;
use App\Services\MailService;
use App\Services\PasswordPolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $rol = Auth::user()->rol;
        info('(routing info): Usuario autenticado con rol: '.$rol->value);
        if ($rol === UserRole::Admin) {
            return redirect()->route('admin.dashboard');
        }
        if ($rol === UserRole::Auditor) {
            return redirect()->route('auditor.dashboard');
        }
        if ($rol === UserRole::Cargador) {
            return redirect()->route('actividades.importar');
        }
        if ($rol === UserRole::Unidad) {
            return redirect()->route('unidad.dashboard');
        }

        return redirect()->route('actividades.historial');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->name('dashboard');

// Rutas de expiración de contraseña (accesibles de forma segura para usuarios deslogueados)
Route::get('/password/expired', function (PasswordPolicyService $policyService) {
    $email = session('expired_user_email');
    $name = session('expired_user_name');

    // Defensa: Si no hay datos temporales de expiración en sesión, denegar acceso inmediato
    if (! $email) {
        return redirect()->route('login');
    }

    $user = User::where('email', $email)->first();
    if (! $user || ! $policyService->isExpired($user)) {
        return redirect()->route('login');
    }

    // Despachar de forma automática el correo de renovación al cargar la pantalla por primera vez
    $failedMail = $policyService->getFailedRenewalMail($user);
    $hasActiveToken = $policyService->hasActiveToken($user->email);

    if (! $failedMail && ! $hasActiveToken) {
        $token = $policyService->generateRenewalToken($user);
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        $expirationString = $policyService->getExpirationDate($user)->format('d-m-Y');

        MailService::sendSafe(
            $user->email,
            new PasswordRenewalMail($user, $url, $expirationString),
            [
                'user_id' => $user->id,
                'url' => $url,
                'expiration_string' => $expirationString,
            ]
        );
    }

    $expirationDate = $policyService->getExpirationDate($user)->format('d-m-Y');

    return view('auth.password-expired', [
        'user' => $user,
        'expirationDate' => $expirationDate,
    ]);
})->name('password.expired');

Route::post('/password/request-renewal', function (Request $request, PasswordPolicyService $policyService) {
    $email = session('expired_user_email');

    // Defensa: Asegurar contexto síncrono de sesión
    if (! $email) {
        return redirect()->route('login');
    }

    $user = User::where('email', $email)->first();
    if (! $user) {
        return redirect()->route('login');
    }

    // 1. Identificar si existe una petición idéntica fallida para reintentarla de inmediato
    $failedMail = $policyService->getFailedRenewalMail($user);
    if ($failedMail) {
        if ($failedMail->sendSynchronously()) {
            return back()->with('success', 'Se ha reintentado enviar el enlace seguro de renovación a su correo electrónico institucional.');
        } else {
            return back()->with('error', 'El reintento de envío síncrono falló. Por favor, compruebe la conectividad del servidor SMTP.');
        }
    }

    // 2. Si no hay fallos pero el token sigue activo, significa que ya fue enviado correctamente
    if ($policyService->hasActiveToken($user->email)) {
        return back()->with('success', 'Revisa tu correo electrónico. Ya existe un enlace de renovación activo.');
    }

    // 3. De lo contrario, iniciar una petición limpia de renovación
    $token = $policyService->generateRenewalToken($user);
    $reason = is_null($user->password_changed_at) ? 'first_login' : 'renewal';

    $url = url(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
        'reason' => $reason,
    ], false));

    $expirationString = $policyService->getExpirationDate($user)->format('d-m-Y');

    $sent = MailService::sendSafe(
        $user->email,
        new PasswordRenewalMail($user, $url, $expirationString),
        [
            'user_id' => $user->id,
            'url' => $url,
            'expiration_string' => $expirationString,
        ]
    );

    if ($sent) {
        return back()->with('success', 'Se ha enviado un nuevo enlace seguro de renovación a su correo electrónico institucional.');
    }

    return back()->with('error', 'El envío de correo falló síncronamente. Por favor, intente nuevamente más tarde.');
})->name('password.request-renewal');

Route::middleware(['auth'])->group(function () {
    // Endpoint síncrono ligero para el Keep-Alive de sesión activa (Heartbeat)
    Route::post('/session/keep-alive', function () {
        return response()->json([
            'status' => 'active',
            'refreshed_at' => now()->toIso8601String(),
        ]);
    })->name('session.keep-alive');

    // Descarga segura de archivos verificadores (Almacenamiento Privado)
    Route::get('/archivos/{archivo}/descargar', [DescargaVerificadorController::class, 'descargar'])
        ->name('archivos.descargar');

    // Historial global: Accesible por todos los roles autenticados (Renombrado de Consulta a Historial)
    Route::get('/historial', [ActividadController::class, 'historial'])
        ->middleware('role:admin,director,auditor,cargador,unidad')
        ->name('actividades.historial');

    // Módulo de Correos Fallidos compartido para Auditor y Administrador
    Route::get('/correos-fallidos', function () {
        return view('auditor.failed-mails');
    })->middleware('role:admin,auditor')->name('auditor.correos-fallidos');

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
            if (auth()->user()->rol !== UserRole::Auditor) {
                abort(403, 'Solo el rol de auditor puede despachar renotificaciones.');
            }

            // Intentar envío síncrono seguro
            $sent = MailService::sendSafe(
                $unidad->user->email,
                new NuevasActividadesPendientes($unidad),
                ['unidad_id' => $unidad->id]
            );

            if ($sent) {
                return back()->with('success', "Se ha enviado una nueva renotificación de forma síncrona a la unidad '{$unidad->user->name}'.");
            }

            return back()->with('error', "El envío falló de forma síncrona. Se ha archivado la renotificación en 'Correos Fallidos' para su posterior gestión.");
        })->name('auditor.unidades.renotificar');
    });

    // Rutas exclusivas del Director Regional
    Route::middleware(['role:director'])->group(function () {
        Route::get('/director/dashboard', function () {
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

            // 2. Catálogo de unidades asignadas ordenadas por menor avance a mayor avance (Excluyendo unidades sin actividades)
            $unidadesEstadisticas = Unidad::query()
                ->with(['user'])
                ->where('region_id', $region?->id)
                ->get()
                ->map(function ($unidad) use ($selectedYear, $selectedMonth, $view) {
                    $cargadas = Actividad::where('unidad_id_asignada', $unidad->id)
                        ->where('estado', 'CARGADA')
                        ->when($view !== 'global', function ($q) use ($selectedYear) {
                            $q->where('AÑO', $selectedYear);
                        })
                        ->when($view === 'mes', function ($q) use ($selectedMonth) {
                            $q->where('MES', $selectedMonth);
                        })
                        ->count();

                    $verificadas = Actividad::where('unidad_id_asignada', $unidad->id)
                        ->where('estado', 'VERIFICADA')
                        ->when($view !== 'global', function ($q) use ($selectedYear) {
                            $q->where('AÑO', $selectedYear);
                        })
                        ->when($view === 'mes', function ($q) use ($selectedMonth) {
                            $q->where('MES', $selectedMonth);
                        })
                        ->count();

                    $total = $cargadas + $verificadas;

                    if ($total === 0) {
                        return null;
                    }

                    $avance = $verificadas === 0 ? 0 : round(($verificadas / $total) * 100, 1);

                    // Recuperar únicamente las actividades verificadas con archivos asociados que corresponden al periodo actual
                    $actividadesVerificadas = Actividad::with(['archivos'])
                        ->where('unidad_id_asignada', $unidad->id)
                        ->where('estado', 'VERIFICADA')
                        ->when($view !== 'global', function ($q) use ($selectedYear) {
                            $q->where('AÑO', $selectedYear);
                        })
                        ->when($view === 'mes', function ($q) use ($selectedMonth) {
                            $q->where('MES', $selectedMonth);
                        })
                        ->orderBy('FECHA', 'desc')
                        ->get();

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
            $queryActividades = Actividad::with(['archivos', 'unidadAsignada'])
                ->where('activo', true)
                ->forUser($user);

            if ($view !== 'global') {
                $queryActividades->where('AÑO', $selectedYear);

                if ($view === 'mes') {
                    $queryActividades->where('MES', $selectedMonth);
                }
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
        })->name('director.dashboard');

        // Acción de renotificación regional asíncrona
        Route::post('/director/unidades/{unidad}/renotificar', function (Unidad $unidad) {
            $region = Region::where('user_id', Auth::id())->first();
            if (! $region || $unidad->region_id !== $region->id) {
                abort(403, 'No tiene permisos para renotificar unidades fuera de su jurisdicción.');
            }

            $sent = MailService::sendSafe(
                $unidad->user->email,
                new NuevasActividadesPendientes($unidad),
                ['unidad_id' => $unidad->id]
            );

            if ($sent) {
                return back()->with('success', "Se ha enviado una nueva renotificación de forma síncrona a la unidad '{$unidad->user->name}'.");
            }

            return back()->with('error', "El envío síncrono falló. Se ha archivado la renotificación en 'Correos Fallidos' para posterior gestión administrativa.");
        })->name('director.unidades.renotificar');
    });

    //  Rutas exclusivas de Administración
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
        Route::get('/admin/usuarios', function () {
            $search = request('search');
            $usuarios = User::query()
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderBy('rol', 'asc')
                ->orderBy('name', 'asc')
                ->paginate(15);

            $regiones = Region::all();

            return view('admin.edicion', compact('usuarios', 'search', 'regiones'));
        })->name('admin.usuarios');

        // Creación de Región (Incluye creación automática de Director Regional)
        Route::post('/admin/crear-region', function (Request $request) {
            if (! session('modo_edicion')) {
                abort(403, 'Acción bloqueada. Debe activar el Modo Edición.');
            }

            $request->validate([
                'region_nombre' => 'required|string|max:50',
                'director_nombre' => 'required|string|max:255',
                'director_email' => 'required|email|unique:users,email',
                'region_id' => 'nullable|integer|unique:region,id',
            ]);

            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->director_nombre,
                    'email' => $request->director_email,
                    'password' => Hash::make('password'),
                    'rol' => 'director',
                    'activo' => true,
                    'password_changed_at' => null,
                ]);

                Region::create([
                    'id' => $request->region_id ?: null,
                    'region_nombre' => $request->region_nombre,
                    'user_id' => $user->id,
                ]);
            });

            return back()->with('success', "La Región '{$request->region_nombre}' y su Director Regional han sido creados con éxito.");
        })->name('admin.crear-region');

        // Creación de Unidad Operativa (Incluye creación automática de Operador de Unidad)
        Route::post('/admin/crear-unidad', function (Request $request) {
            if (! session('modo_edicion')) {
                abort(403, 'Acción bloqueada. Debe activar el Modo Edición.');
            }

            $request->validate([
                'unidad_nombre' => 'required|string|max:255',
                'unidad_email' => 'required|email|unique:users,email',
                'region_id' => 'required|exists:region,id',
                'unidad_id' => 'nullable|integer|unique:unidad,id',
            ]);

            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->unidad_nombre,
                    'email' => $request->unidad_email,
                    'password' => Hash::make('password'),
                    'rol' => 'unidad',
                    'activo' => true,
                    'password_changed_at' => null,
                ]);

                Unidad::create([
                    'id' => $request->unidad_id ?: null,
                    'region_id' => $request->region_id,
                    'user_id' => $user->id,
                ]);
            });

            return back()->with('success', "La Unidad '{$request->unidad_nombre}' y su Operador han sido creados con éxito.");
        })->name('admin.crear-unidad');

        // Creación de Usuario de Sistema (Admin, Auditor o Cargador)
        Route::post('/admin/crear-usuario', function (Request $request) {
            if (! session('modo_edicion')) {
                abort(403, 'Acción bloqueada. Debe activar el Modo Edición.');
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'rol' => 'required|in:admin,auditor,cargador',
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password'),
                'rol' => $request->rol,
                'activo' => true,
                'password_changed_at' => null,
            ]);

            return back()->with('success', "Usuario '{$request->name}' con rol '{$request->rol}' creado con éxito.");
        })->name('admin.crear-usuario');

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
                'activo' => ! $user->activo,
            ]);

            $statusText = $user->activo ? 'habilitada' : 'deshabilitada';

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

<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuditorDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DescargaVerificadorController;
use App\Http\Controllers\DirectorDashboardController;
use App\Http\Controllers\PasswordRenewalController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'permission:usuarios.crear|historial.ver-global|historial.ver-regional|actividades.verificar|actividades.importar'])
    ->name('dashboard');

// Redirecciones de compatibilidad para evitar colisiones con URLs heredadas o marcadores de usuario
Route::get('/admin/dashboard', function () {
    return redirect()->route('dashboard');
});
Route::get('/auditor/dashboard', function () {
    return redirect()->route('dashboard');
});
Route::get('/director/dashboard', function () {
    return redirect()->route('dashboard');
});
Route::get('/unidad/dashboard', function () {
    return redirect()->route('dashboard');
});

// Rutas de expiración de contraseña (accesibles de forma segura para usuarios deslogueados)
Route::get('/password/expired', [PasswordRenewalController::class, 'showExpired'])->name('password.expired');
Route::post('/password/request-renewal', [PasswordRenewalController::class, 'requestRenewal'])->name('password.request-renewal');

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

    // Historial global: Protegido dinámicamente si cuenta con alguna de las capacidades de consulta
    Route::get('/historial', [ActividadController::class, 'historial'])
        ->middleware('permission:historial.ver-global|historial.ver-regional|historial.ver-unidad|actividades.importar')
        ->name('actividades.historial');

    // Módulo de Correos Fallidos compartido
    Route::get('/correos-fallidos', function () {
        return view('auditor.failed-mails');
    })->middleware('permission:correos.ver-historial')->name('auditor.correos-fallidos');

    // Rutas de Auditoría
    Route::middleware(['permission:historial.ver-global'])->group(function () {
        Route::get('/auditor/dashboard', AuditorDashboardController::class)->name('auditor.dashboard');
        Route::post('/auditor/unidades/{unidad}/renotificar', [AuditorDashboardController::class, 'renotificarUnidad'])->name('auditor.unidades.renotificar');
    });

    // Rutas del Director Regional
    Route::middleware(['permission:historial.ver-regional'])->group(function () {
        Route::get('/director/dashboard', [DirectorDashboardController::class, 'index'])->name('director.dashboard');
        Route::post('/director/unidades/{unidad}/renotificar', [DirectorDashboardController::class, 'renotificarUnidad'])->name('director.unidades.renotificar');
    });

    // Rutas de Administración Crítica
    Route::middleware(['permission:usuarios.crear'])->group(function () {
        Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
        Route::get('/admin/actividades', [ActividadController::class, 'historial'])->name('admin.actividades');

        // Catálogo de usuarios
        Route::get('/admin/usuarios', [AdminUserController::class, 'index'])->name('admin.usuarios');

        // Mutaciones de infraestructura y accesos
        Route::post('/admin/crear-region', [AdminUserController::class, 'crearRegion'])->name('admin.crear-region');
        Route::post('/admin/crear-unidad', [AdminUserController::class, 'crearUnidad'])->name('admin.crear-unidad');
        Route::post('/admin/crear-usuario', [AdminUserController::class, 'crearUsuario'])->name('admin.crear-usuario');

        // Controles de Modo Edición
        Route::get('/admin/edicion', [AdminUserController::class, 'entrarEdicion'])->middleware('password.confirm')->name('admin.edicion');
        Route::get('/admin/salir-edicion', [AdminUserController::class, 'salirEdicion'])->name('admin.salir-edicion');
        Route::patch('/admin/usuarios/{user}/toggle', [AdminUserController::class, 'toggleUsuario'])->name('admin.usuarios.toggle');
    });

    // Rutas de Carga Masiva (Excel)
    Route::middleware(['permission:actividades.importar'])->group(function () {
        Route::get('/actividades/importar', function () {
            return view('actividades.import');
        })->name('actividades.importar');
    });

    // Rutas de Unidades Operativas
    Route::middleware(['permission:actividades.verificar'])->group(function () {
        Route::get('/unidad/dashboard', function () {
            return view('unidad.dashboard');
        })->name('unidad.dashboard');
    });
});

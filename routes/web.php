<?php

use App\Enums\UserRole;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuditorDashboardController;
use App\Http\Controllers\DescargaVerificadorController;
use App\Http\Controllers\DirectorDashboardController;
use App\Http\Controllers\PasswordRenewalController;
use Illuminate\Support\Facades\Auth;
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
        Route::get('/auditor/dashboard', AuditorDashboardController::class)->name('auditor.dashboard');
        Route::post('/auditor/unidades/{unidad}/renotificar', [AuditorDashboardController::class, 'renotificarUnidad'])->name('auditor.unidades.renotificar');
    });
    // Rutas exclusivas del Director Regional
    Route::middleware(['role:director'])->group(function () {
        Route::get('/director/dashboard', [DirectorDashboardController::class, 'index'])->name('director.dashboard');
        Route::post('/director/unidades/{unidad}/renotificar', [DirectorDashboardController::class, 'renotificarUnidad'])->name('director.unidades.renotificar');
    });

    //  Rutas exclusivas de Administración
    Route::middleware(['role:admin'])->group(function () {
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

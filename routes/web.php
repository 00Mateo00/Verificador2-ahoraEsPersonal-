<?php

use App\Http\Controllers\ActividadController;
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

        return redirect()->route('actividades.index');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Consulta global: Accesible por todos los roles autenticados (TO-DO : esta vista ya debería llamarse "historial")
    Route::get('/actividades', [ActividadController::class, 'index'])
        ->middleware('role:admin,director,auditor,cargador,unidad')
        ->name('actividades.index');

    // Rutas exclusivas de Administración
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/admin/actividades', [ActividadController::class, 'index'])->name('admin.actividades');
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

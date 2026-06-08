<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        $rol = Auth::user()->rol;
        info("(routing info): Usuario autenticado con rol: $rol");
        if ($rol === 'admin') {
            return redirect()->route('admin.actividades');
        }
        if ($rol === 'cargador') {
            return redirect()->route('actividades.importar');
        }
        /*  return redirect()->route('actividades.index'); */
    }
    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->name('dashboard');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');

// en el futuro el login será manejado por la API de ClaveUnica (TO-DO)
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');


Route::middleware(['auth'])->group(function () {
    Route::get('/admin/actividades', function () {
        return 'Admin Dashboard Stub';
    })->name('admin.actividades');

    Route::get('/actividades/create', [\App\Http\Controllers\ActividadController::class, 'create'])->name('actividades.create');

    Route::get('/actividades/importar', function () {
        return view('actividades.import');
    })->name('actividades.importar');

    Route::get('/actividades', [\App\Http\Controllers\ActividadController::class, 'index'])->name('actividades.index');
});

require __DIR__ . '/settings.php';

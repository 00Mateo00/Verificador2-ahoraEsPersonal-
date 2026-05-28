<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->usuario_rol === 'admin') {
            return redirect()->route('admin.actividades');
        }
        return redirect()->route('actividades.create');
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
    Route::get('/admin/actividades', function () { return 'Admin Dashboard Stub'; })->name('admin.actividades');

    Route::get('/actividades/create', [\App\Http\Controllers\ActividadController::class, 'create'])->name('actividades.create');

    Route::get('/actividades', [\App\Http\Controllers\ActividadController::class, 'index'])->name('actividades.index');

});


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
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth'); // Fallback para enlaces GET legacy

Route::middleware(['auth'])->group(function () {
    // Stubs que reemplazaremos en los siguientes pasos
    Route::get('/admin/actividades', function () { return 'Admin Dashboard Stub'; })->name('admin.actividades');
    Route::get('/actividades/create', function () { return 'Crear Actividad Stub'; })->name('actividades.create');
});


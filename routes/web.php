<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth'); // Fallback para enlaces GET legacy

Route::middleware(['auth'])->group(function () {
    // Stubs que reemplazaremos en los siguientes pasos
    Route::get('/admin/actividades', function () { return 'Admin Dashboard Stub'; })->name('admin.actividades');
    Route::get('/actividades/create', function () { return 'Crear Actividad Stub'; })->name('actividades.create');
});


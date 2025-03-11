<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DockerController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



// System Routes
Route::group(['middleware' => ['auth', 'verified',\App\Http\Middleware\ForceEnableOtp::class]], function () {
    Route::get('/system/users', [\App\Http\Controllers\System\UsersController::class, 'index'])->name('users.index');
    Route::get('/system/users/create', [\App\Http\Controllers\System\UsersController::class, 'create'])->name('users.create');
    Route::put('/system/users/{user}/profile', [\App\Http\Controllers\System\UsersController::class, 'editProfile'])->name('users.editProfile');
    Route::put('/system/users/{user}/password', [\App\Http\Controllers\System\UsersController::class, 'editPassword'])->name('users.editPassword');
    Route::put('/system/users/{user}/permissions', [\App\Http\Controllers\System\UsersController::class, 'editPermissions'])->name('users.editPermissions');
    Route::get('/system/users/{user}', [\App\Http\Controllers\System\UsersController::class, 'edit'])->name('users.show');
    Route::post('/system/users', [\App\Http\Controllers\System\UsersController::class, 'store'])->name('users.store');
    Route::delete('/system/users/{user}', [\App\Http\Controllers\System\UsersController::class, 'destroy'])->name('users.delete');

    Route::get('/system/roles', [\App\Http\Controllers\System\RolesController::class, 'index'])->name('roles.index');
    Route::get('/system/roles/create', [\App\Http\Controllers\System\RolesController::class, 'create'])->name('roles.create');
    Route::post('/system/roles', [\App\Http\Controllers\System\RolesController::class, 'store'])->name('roles.store');
    Route::get('/system/roles/{role}', [\App\Http\Controllers\System\RolesController::class, 'show'])->name('roles.show');
    Route::put('/system/roles/{role}', [\App\Http\Controllers\System\RolesController::class, 'update'])->name('roles.update');
    Route::patch('/system/roles/{role}', [\App\Http\Controllers\System\RolesController::class, 'empty'])->name('roles.empty');
    Route::delete('/system/roles/{role}', [\App\Http\Controllers\System\RolesController::class, 'destroy'])->name('roles.delete');

    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// This one is not in the above group, because it's the two factor setup page
Route::get('/no2fa',[\App\Http\Controllers\Auth\TwoFactorController::class, 'notEnabled'])->name('two-factor.enable');
Route::get('/two-factor/enable', [\App\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->middleware(['auth', 'verified'])->name('two-factor.index');
Route::get('/two-factor/qrcode', [\App\Http\Controllers\Auth\TwoFactorController::class, 'showQRCode'])->middleware(['auth', 'verified'])->name('two-factor.qrcode');
Route::post('/two-factor/verify', [\App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->middleware(['auth', 'verified'])->name('two-factor.verify');
Route::get('/two-factor/login', [\App\Http\Controllers\Auth\TwoFactorController::class, 'login'])->middleware(['auth','verified'])->name('two-factor.login');
Route::delete('/two-factor/enable', [\App\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->middleware(['auth', 'verified'])->name('two-factor.disable');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

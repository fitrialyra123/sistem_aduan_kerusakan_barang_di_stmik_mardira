<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('complaints', ComplaintController::class);

    Route::middleware(['auth', 'role:dev'])->group(function(){
        Route::post('system-logs/clear', [SystemLogController::class , 'clearOldLogs'])->name('system-logs.clear');
        Route::get('system-logs/export', [SystemLogController::class, 'export'])->name('system-logs.export'); 
        Route::resource('system-logs', SystemLogController::class);
    });
});


Route::middleware(['auth', 'role:admin,dev'])->group(function() {
    Route::resource('locations', LocationController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::patch('users/{user}/unverify', [UserController::class, 'unverify'])->name('users.unverify');

});

require __DIR__.'/auth.php';

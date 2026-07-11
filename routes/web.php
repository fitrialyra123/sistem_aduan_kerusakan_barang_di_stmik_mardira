<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemLogController;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    Route::resource('location', LocationController::class);
    Route::resource('categories', CategoryController::class);
});

require __DIR__.'/auth.php';

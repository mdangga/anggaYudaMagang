<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestLocationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Homepage');
});

Route::get('/get-location', [LocationController::class, 'getData'])->name('get-location');

Route::prefix('request-locations')->group(function () {
    Route::get('/create', [RequestLocationController::class, 'create'])->name('request-locations.create');
    Route::post('/', [RequestLocationController::class, 'store'])->name('request-locations.store');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::prefix('request-locations')->group(function () {
        Route::get('/get-location', [RequestLocationController::class, 'index'])->name('request-locations.index');
        Route::post('/{id}/approve', [RequestLocationController::class, 'approve'])->name('request-locations.approve');
        Route::post('/{id}/destroy', [RequestLocationController::class, 'destroy'])->name('request-locations.destroy');
    });

    Route::prefix('categories')->group(function () {
        Route::get('/get-categories', [CategoryController::class, 'index'])->name('categories.index');
    });
});

require __DIR__ . '/auth.php';

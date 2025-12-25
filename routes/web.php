<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileWebController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/* 
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|*/

// Route::get('/ping', function () {
//     return response('pong');
// });

// Route::get('/ping-raw', fn () => 'pong')
//     ->withoutMiddleware(VerifyCsrfToken::class);

//? routes without authentication
// route untuk homepage
Route::get('/', function () {
    return Inertia::render('Homepage');
});
// route untuk location data
Route::prefix('locations')->group(function () {
    Route::get('/get-locations', [LocationController::class, 'getData'])->name('get-location');
    Route::get('/get-locations/{id}', [LocationController::class, 'getDataById'])->name('get-location-by-id');
    Route::get('/create', [LocationController::class, 'create'])->middleware('signed')->name('locations.create');
    Route::post('/', [LocationController::class, 'store'])->name('locations.store');
});

//? routes with authentication
Route::middleware('auth')->group(function () {
    // dashboard route
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // profile routes
    Route::prefix('profile-web')->group(function () {
        Route::get('/', [ProfileWebController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileWebController::class, 'update'])->name('profile.update');
    });

    // location management routes
    Route::prefix('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('locations.index');
        Route::get('/ajax', [LocationController::class, 'ajax']);
        Route::post('/generate-link', [LocationController::class, 'generate'])->name('locations.generate-link');
        Route::post('/{id}/approve', [LocationController::class, 'approve'])->name('locations.approve');
        Route::delete('/{id}', [LocationController::class, 'destroy'])->name('locations.destroy');
    });

    // category management routes
    Route::prefix('categories')->group(function () {
        Route::get('/get-categories', [CategoryController::class, 'getStatCategories'])->name('category.getStat');
        Route::get('/', [CategoryController::class, 'index'])->name('category.index');
        Route::get('/ajax', [CategoryController::class, 'ajax']);
        Route::get('/create', [CategoryController::class, 'create'])->name('category.create');
        Route::post('/', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/edit/{id}', [CategoryController::class, 'edit'])->name('category.edit');
        Route::patch('/{id}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');
    });

    // department management routes
    Route::prefix('jurusan')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('department.index');
        Route::get('/ajax', [DepartmentController::class, 'ajax']);
        Route::get('/create', [DepartmentController::class, 'create'])->name('department.create');
        Route::post('/', [DepartmentController::class, 'store'])->name('department.store');
        Route::get('/edit/{id}', [DepartmentController::class, 'edit'])->name('department.edit');
        Route::patch('/{id}', [DepartmentController::class, 'update'])->name('department.update');
        Route::delete('/{id}', [DepartmentController::class, 'destroy'])->name('department.destroy');
    });

    // faculty management routes
    Route::prefix('fakultas')->group(function () {
        Route::get('/', [FacultyController::class, 'index'])->name('faculty.index');
        Route::get('/ajax', [FacultyController::class, 'ajax']);
        Route::get('/create', [FacultyController::class, 'create'])->name('faculty.create');
        Route::post('/', [FacultyController::class, 'store'])->name('faculty.store');
        Route::get('/edit/{id}', [FacultyController::class, 'edit'])->name('faculty.edit');
        Route::patch('/{id}', [FacultyController::class, 'update'])->name('faculty.update');
        Route::delete('/destroy/{id}', [FacultyController::class, 'destroy'])->name('faculty.destroy');
    });
});

require __DIR__ . '/auth.php';

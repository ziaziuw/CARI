<?php

use App\Http\Controllers\PointsController;
use App\Http\Controllers\PolylinesController;
use App\Http\Controllers\PolygonsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('points', PointsController::class);
Route::resource('polylines', PolylinesController::class);
Route::resource('polygons', PolygonsController::class);

//peta tidak boleh diakses umum
//auth artinya sudah berhasil login, jd harus login terus script verified untuk akun yang telah terverifikasi
Route::get('/map', [PointsController::class, 'index'])->middleware(['auth', 'verified'])->name('map');
//tabel boleh diakses umum
Route::get('/table', [TableController::class, 'index'])->name('table');
Route::post('points/{id}/claim', [PointsController::class,'claim'])->name('points.claim');



require __DIR__ . '/auth.php';

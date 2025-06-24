<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\PolylinesController;
use App\Http\Controllers\PolygonsController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

// Route utama/home
Route::get('/', function () {
    return view('home', ['title' => 'Home']);
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grup untuk rute yang memerlukan autentikasi & verifikasi
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute peta hanya untuk user login
    Route::get('/map', [PointsController::class, 'index'])->name('map');

    // Klaim item oleh user login
    Route::post('points/{id}/claim', [PointsController::class, 'claim'])->name('points.claim');

    // fix auth untuk halaman tabel
    Route::get('/table', [TableController::class, 'index'])->name('table');

    // Rute CRUD untuk data spasial, harus login agar user_id tersimpan
    Route::resource('points', PointsController::class);
    Route::resource('polylines', PolylinesController::class);
    Route::resource('polygons', PolygonsController::class);
});



// Tambahkan route untuk API
Route::get('/api/points', [PointsController::class, 'apiPoints'])->name('api.points');

Route::get('/debug-storage', function () {
    $storagePath = storage_path('app/public/images');
    $publicStoragePath = public_path('storage');
    $imagesPath = public_path('storage/images');

    // Safe readlink function for Windows
    $linkTarget = 'Not a link';
    if (is_link($publicStoragePath)) {
        try {
            $linkTarget = readlink($publicStoragePath);
        } catch (Exception $e) {
            $linkTarget = 'Link exists but cannot read target (Windows compatibility issue)';
        }
    }

    // Get images list
    $images = [];
    if (is_dir($imagesPath)) {
        $imageFiles = glob($imagesPath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        $images = array_map('basename', $imageFiles);
    }

    return [
        'storage_exists' => is_dir($storagePath),
        'public_link_exists' => is_link($publicStoragePath) || is_dir($publicStoragePath),
        'public_link_target' => $linkTarget,
        'images_accessible' => is_dir($imagesPath),
        'images_count' => count($images),
        'images' => $images,
        'storage_path' => $storagePath,
        'public_storage_path' => $publicStoragePath,
        'images_path' => $imagesPath,
        'link_type' => is_link($publicStoragePath) ? 'symbolic' : (is_dir($publicStoragePath) ? 'directory' : 'none'),
        'test_file_exists' => file_exists($imagesPath . '/test.txt'),
        'sample_urls' => [
            'storage_url' => '/storage/images/sample.jpg',
            'asset_url' => url('/storage/images/sample.jpg')
        ]
    ];
});
require __DIR__ . '/auth.php';

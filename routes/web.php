<?php
// Update routes/web.php - Complete master data routes

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\MasterDataController;

// Public routes
Route::get('/', [PublicController::class, 'index'])->name('public.index');
Route::get('/form-permohonan', [PublicController::class, 'showForm'])->name('public.form');
Route::post('/form-permohonan', [PublicController::class, 'store'])->name('public.store');

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/permohonan', [PermohonanController::class, 'index'])->name('permohonan.index');
    Route::post('/permohonan', [PermohonanController::class, 'store'])->name('permohonan.store');
    Route::patch('/permohonan/{id}/status', [PermohonanController::class, 'updateStatus'])->name('permohonan.status');
    
    Route::get('/print-permohonan', [PermohonanController::class, 'print'])->name('permohonan.print');
    Route::get('/print-permohonan/{id}', [PermohonanController::class, 'printSingle'])->name('permohonan.print.single');
    
    // Master Data Routes - accessible based on access level
    Route::middleware(['access.level:0,1'])->group(function () {
        // Kampus routes
        Route::get('master/kampus', [MasterDataController::class, 'kampusIndex'])->name('master.kampus.index');
        Route::post('master/kampus', [MasterDataController::class, 'kampusStore'])->name('master.kampus.store');
        Route::put('master/kampus/{id}', [MasterDataController::class, 'kampusUpdate'])->name('master.kampus.update');
        Route::delete('master/kampus/{id}', [MasterDataController::class, 'kampusDestroy'])->name('master.kampus.destroy');
        
        // Unit routes
        Route::get('master/unit', [MasterDataController::class, 'unitIndex'])->name('master.unit.index');
        Route::post('master/unit', [MasterDataController::class, 'unitStore'])->name('master.unit.store');
        Route::put('master/unit/{id}', [MasterDataController::class, 'unitUpdate'])->name('master.unit.update');
        Route::delete('master/unit/{id}', [MasterDataController::class, 'unitDestroy'])->name('master.unit.destroy');
        
        // Sub Unit routes
        Route::get('master/sub-unit', [MasterDataController::class, 'subUnitIndex'])->name('master.sub-unit.index');
        Route::post('master/sub-unit', [MasterDataController::class, 'subUnitStore'])->name('master.sub-unit.store');
        Route::put('master/sub-unit/{id}', [MasterDataController::class, 'subUnitUpdate'])->name('master.sub-unit.update');
        Route::delete('master/sub-unit/{id}', [MasterDataController::class, 'subUnitDestroy'])->name('master.sub-unit.destroy');
        
        // Jenis Perangkat routes
        Route::get('master/jenis-perangkat', [MasterDataController::class, 'jenisPerangkatIndex'])->name('master.jenis-perangkat.index');
        Route::post('master/jenis-perangkat', [MasterDataController::class, 'jenisPerangkatStore'])->name('master.jenis-perangkat.store');
        Route::put('master/jenis-perangkat/{id}', [MasterDataController::class, 'jenisPerangkatUpdate'])->name('master.jenis-perangkat.update');
        Route::delete('master/jenis-perangkat/{id}', [MasterDataController::class, 'jenisPerangkatDestroy'])->name('master.jenis-perangkat.destroy');
        
        // Jenis Perawatan routes
        Route::get('master/jenis-perawatan', [MasterDataController::class, 'jenisPerawatanIndex'])->name('master.jenis-perawatan.index');
        Route::post('master/jenis-perawatan', [MasterDataController::class, 'jenisPerawatanStore'])->name('master.jenis-perawatan.store');
        Route::put('master/jenis-perawatan/{id}', [MasterDataController::class, 'jenisPerawatanUpdate'])->name('master.jenis-perawatan.update');
        Route::delete('master/jenis-perawatan/{id}', [MasterDataController::class, 'jenisPerawatanDestroy'])->name('master.jenis-perawatan.destroy');
        
        // Detail Perawatan routes
        Route::get('master/detail-perawatan', [MasterDataController::class, 'detailPerawatanIndex'])->name('master.detail-perawatan.index');
        Route::post('master/detail-perawatan', [MasterDataController::class, 'detailPerawatanStore'])->name('master.detail-perawatan.store');
        Route::put('master/detail-perawatan/{id}', [MasterDataController::class, 'detailPerawatanUpdate'])->name('master.detail-perawatan.update');
        Route::delete('master/detail-perawatan/{id}', [MasterDataController::class, 'detailPerawatanDestroy'])->name('master.detail-perawatan.destroy');
        
        // Perangkat Terdaftar routes
        Route::get('master/perangkat-terdaftar', [MasterDataController::class, 'perangkatTerdaftarIndex'])->name('master.perangkat-terdaftar.index');
        Route::post('master/perangkat-terdaftar', [MasterDataController::class, 'perangkatTerdaftarStore'])->name('master.perangkat-terdaftar.store');
        Route::put('master/perangkat-terdaftar/{id}', [MasterDataController::class, 'perangkatTerdaftarUpdate'])->name('master.perangkat-terdaftar.update');
        Route::delete('master/perangkat-terdaftar/{id}', [MasterDataController::class, 'perangkatTerdaftarDestroy'])->name('master.perangkat-terdaftar.destroy');
    });
    
    // Super Admin only - User Management
    Route::middleware(['access.level:0'])->group(function () {
        Route::get('master/user', [MasterDataController::class, 'userIndex'])->name('master.user.index');
        Route::post('master/user', [MasterDataController::class, 'userStore'])->name('master.user.store');
        Route::put('master/user/{id}', [MasterDataController::class, 'userUpdate'])->name('master.user.update');
        Route::delete('master/user/{id}', [MasterDataController::class, 'userDestroy'])->name('master.user.destroy');
    });
});
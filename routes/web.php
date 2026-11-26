<?php
// routes/web.php - Complete routes with polling endpoint

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\KehadiranSyncController;
use App\Http\Controllers\PasskeyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

// Public routes
Route::get('/', [PublicController::class, 'index'])->name('public.index');
Route::get('/form-permohonan', [PublicController::class, 'showForm'])->name('public.form');
Route::post('/form-permohonan', [PublicController::class, 'store'])->name('public.store');
Route::get('/permohonan/latest', [PublicController::class, 'getLatest'])->name('public.latest'); // New polling endpoint

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Passkey (fingerprint / Face ID) login: passkeys registered here, or in Kehadiran
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/passkey/options', [PasskeyController::class, 'options'])->name('passkey.options');
    Route::post('/passkey/login', [PasskeyController::class, 'login'])->middleware('guest')->name('passkey.login');
    Route::post('/passkey/kehadiran/options', [PasskeyController::class, 'kehadiranOptions'])->name('passkey.kehadiran.options');
    Route::post('/passkey/kehadiran/login', [PasskeyController::class, 'kehadiranLogin'])->middleware('guest')->name('passkey.kehadiran.login');
});

// Kehadiran pushes the Direktorat TI&TD roster and passkeys here (HMAC-signed, no session)
Route::post('/kehadiran/sync', KehadiranSyncController::class)
    ->withoutMiddleware([PreventRequestForgery::class])
    ->middleware('throttle:30,1')
    ->name('kehadiran.sync');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/passkey/confirm', [PasskeyController::class, 'confirm'])->middleware('throttle:10,1')->name('passkey.confirm');
    Route::post('/profil/passkeys/options', [PasskeyController::class, 'registerOptions'])->middleware('throttle:10,1')->name('passkeys.options');
    Route::post('/profil/passkeys', [PasskeyController::class, 'store'])->middleware('throttle:10,1')->name('passkeys.store');
    Route::delete('/profil/passkeys/{passkey}', [PasskeyController::class, 'destroy'])->name('passkeys.destroy');
    
    Route::get('/permohonan', [PermohonanController::class, 'index'])->name('permohonan.index');
    Route::post('/permohonan', [PermohonanController::class, 'store'])->name('permohonan.store');
    Route::patch('/permohonan/{id}/status', [PermohonanController::class, 'updateStatus'])->name('permohonan.status');
    
    Route::post('/permohonan/{id}/laporan', [PermohonanController::class, 'storeLaporan'])->name('permohonan.laporan.store');
    Route::get('/permohonan/{id}/laporan', [PermohonanController::class, 'showLaporan'])->name('permohonan.laporan.show');

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
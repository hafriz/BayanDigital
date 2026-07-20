<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MasjidController as AdminMasjidController;
use App\Http\Controllers\Admin\ScreenContentController as AdminScreenContentController;
use App\Http\Controllers\Admin\ScreenDeviceController as AdminScreenDeviceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Web\AndroidDownloadController;
use App\Http\Controllers\Web\LandingPageController;
use App\Http\Controllers\Web\MasjidRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('landing');
Route::get('/android/download', AndroidDownloadController::class)->name('android.download');
Route::get('/register', [MasjidRegistrationController::class, 'create'])->name('masjids.register');
Route::post('/register', [MasjidRegistrationController::class, 'store'])->name('masjids.store');
Route::get('/register/{publicId}/complete', [MasjidRegistrationController::class, 'registered'])->name('masjids.registered');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware(['auth', 'active'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::resource('masjids', AdminMasjidController::class)->only(['index', 'edit', 'update']);
        Route::resource('masjids.contents', AdminScreenContentController::class)->except(['show']);
        Route::middleware('admin')->group(function () {
            Route::get('masjids/{masjid}/devices', [AdminScreenDeviceController::class, 'index'])->name('masjids.devices.index');
            Route::post('masjids/{masjid}/devices/{device}/approve', [AdminScreenDeviceController::class, 'approve'])->name('masjids.devices.approve');
            Route::post('masjids/{masjid}/devices/{device}/reject', [AdminScreenDeviceController::class, 'reject'])->name('masjids.devices.reject');
            Route::post('masjids/{masjid}/devices/{device}/revoke', [AdminScreenDeviceController::class, 'revoke'])->name('masjids.devices.revoke');
        });
        Route::middleware('admin')->resource('users', AdminUserController::class)->except(['show']);
    });
});

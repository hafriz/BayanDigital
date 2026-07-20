<?php

use App\Http\Controllers\Web\AndroidDownloadController;
use App\Http\Controllers\Web\LandingPageController;
use App\Http\Controllers\Web\MasjidRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('landing');
Route::get('/android/download', AndroidDownloadController::class)->name('android.download');
Route::get('/register', [MasjidRegistrationController::class, 'create'])->name('masjids.register');
Route::post('/register', [MasjidRegistrationController::class, 'store'])->name('masjids.store');
Route::get('/register/{publicId}/complete', [MasjidRegistrationController::class, 'registered'])->name('masjids.registered');

<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FrontendController;

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/flights', 'pages.flights')->name('flights');
Route::get('/hotels', [FrontendController::class, 'hotels'])->name('hotels');
Route::get('/cruises', [FrontendController::class, 'cruises'])->name('cruises');
Route::get('/staycation', [FrontendController::class, 'staycations'])->name('staycations');
Route::get('/packages', [FrontendController::class, 'packages'])->name('packages');
Route::get('/packages/{id}', [FrontendController::class, 'packageDetails'])->name('package.details');
Route::get('/offers', [FrontendController::class, 'offers'])->name('offers');
Route::view('/contact', 'pages.contact')->name('contact');


Route::middleware('auth')->group(function () {
    Route::get('/history', [ProfileController::class, 'history'])->name('history');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

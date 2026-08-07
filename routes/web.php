<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FrontendController;

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/flights', 'pages.flights')->name('flights');
Route::get('/hotels', [FrontendController::class, 'hotels'])->name('hotels');
Route::get('/hotels/{id}', [FrontendController::class, 'hotelDetails'])->name('hotel.details');
Route::get('/cruises', [FrontendController::class, 'cruises'])->name('cruises');
Route::get('/staycation', [FrontendController::class, 'staycations'])->name('staycations');
Route::get('/packages', [FrontendController::class, 'packages'])->name('packages');
Route::get('/packages/{id}', [FrontendController::class, 'packageDetails'])->name('package.details');
Route::get('/offers', [FrontendController::class, 'offers'])->name('offers');
Route::view('/contact', 'pages.contact')->name('contact');

Route::view('/terms-and-conditions', 'pages.terms')->name('terms');
Route::view('/privacy-policy', 'pages.privacy')->name('privacy');
Route::view('/cancellation-policy', 'pages.cancellation')->name('cancellation');
Route::view('/blog', 'pages.blog')->name('blog');
Route::view('/blog/detail', 'pages.blog-details')->name('blog.details');
Route::view('/blog/jaipur', 'pages.blog-details-jaipur')->name('blog.jaipur');
Route::view('/faqs', 'pages.faqs')->name('faqs');
Route::view('/help-center', 'pages.help')->name('help');
Route::middleware('auth')->group(function () {
    Route::get('/history', [ProfileController::class, 'history'])->name('history');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::post('/profile/traveller', [ProfileController::class, 'storeTraveller'])->name('profile.traveller.store');
    Route::patch('/profile/traveller/{traveller}', [ProfileController::class, 'updateTraveller'])->name('profile.traveller.update');
    Route::delete('/profile/traveller/{traveller}', [ProfileController::class, 'deleteTraveller'])->name('profile.traveller.destroy');
    Route::post('/profile/logout-other-devices', [ProfileController::class, 'logoutOtherDevices'])->name('profile.logout-other-devices');
});

require __DIR__.'/auth.php';

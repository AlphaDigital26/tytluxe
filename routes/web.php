<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FrontendController;

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/flights', 'pages.flights')->name('flights');
Route::get('/hotels', [FrontendController::class, 'hotels'])->name('hotels');
Route::get('/wishlist', [FrontendController::class, 'wishlist'])->name('wishlist');
Route::post('/wishlist/lookup', [FrontendController::class, 'wishlistLookup'])->name('wishlist.lookup')->middleware('throttle:30,1');
Route::redirect('/hotels/wishlist', '/wishlist');
Route::get('/hotels/{slug}', [FrontendController::class, 'hotelDetails'])->name('hotel.details');

// Browsing (search, hotel details, live pricing) stays public — only actually
// committing to a booking requires an account, same as every mainstream OTA.
// Throttled per-user: review/book call TripJack's real (rate-limited, costly)
// APIs, so nothing should be able to hammer them just by refreshing/resubmitting.
Route::middleware('auth')->group(function () {
    Route::post('/hotels/{slug}/review', [FrontendController::class, 'reviewRoom'])->name('hotel.review')->middleware('throttle:10,1');
    Route::get('/hotels/{slug}/review', [FrontendController::class, 'showReview'])->name('hotel.review.show');
    Route::post('/hotels/{slug}/book', [FrontendController::class, 'submitBooking'])->name('hotel.book')->middleware('throttle:10,1');
    Route::get('/booking/{reference}/pay', [FrontendController::class, 'showPayment'])->name('hotel.payment.show');
    Route::post('/payment/razorpay/callback', [FrontendController::class, 'razorpayCallback'])->name('payment.razorpay.callback')->middleware('throttle:15,1');
    Route::get('/booking/{reference}', [FrontendController::class, 'bookingConfirmation'])->name('hotel.booking.confirmation');
    Route::get('/booking/{reference}/cancel', [FrontendController::class, 'showCancellation'])->name('hotel.booking.cancel.show');
    Route::post('/booking/{reference}/cancel', [FrontendController::class, 'submitCancellation'])->name('hotel.booking.cancel')->middleware('throttle:5,1');
    Route::get('/booking/{reference}/invoice', [FrontendController::class, 'downloadInvoice'])->name('hotel.booking.invoice')->middleware('throttle:20,1');
});

// Razorpay's server calls this directly — no user session exists here, so it
// can't sit behind 'auth'. Protected instead by its own HMAC signature check
// (RazorpayService::verifyWebhookSignature), same security model as an API key.
// Throttled by IP as a defense-in-depth cap against a flood of forged/replayed
// webhook requests — the signature check is the real gate, this just limits noise.
Route::post('/payment/razorpay/webhook', [FrontendController::class, 'razorpayWebhook'])->name('payment.razorpay.webhook')->middleware('throttle:30,1');
Route::get('/cruises', [FrontendController::class, 'cruises'])->name('cruises');
Route::get('/packages', [FrontendController::class, 'packages'])->name('packages');
Route::get('/packages/{slug}', [FrontendController::class, 'packageDetails'])->name('package.details');
Route::get('/offers', [FrontendController::class, 'offers'])->name('offers');
// Public contact/enquiry form — no auth required, so it's the easiest target
// in this file for a spam bot to hammer.
Route::post('/enquiries', [FrontendController::class, 'storeEnquiry'])->name('enquiries.store')->middleware('throttle:10,1');
Route::view('/contact', 'pages.contact')->name('contact');

Route::view('/terms-and-conditions', 'pages.terms')->name('terms');
Route::view('/privacy-policy', 'pages.privacy')->name('privacy');
Route::view('/cancellation-policy', 'pages.cancellation')->name('cancellation');
Route::get('/blog', [FrontendController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [FrontendController::class, 'blogDetails'])->name('blog.details');
Route::view('/faqs', 'pages.faqs')->name('faqs');
Route::view('/help-center', 'pages.help')->name('help');
Route::middleware('auth')->group(function () {
    Route::get('/history', [ProfileController::class, 'history'])->name('history');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('throttle:10,1');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('throttle:5,1');
    Route::get('/profile/export-data', [ProfileController::class, 'exportData'])->name('profile.export-data')->middleware('throttle:5,1');

    Route::post('/profile/traveller', [ProfileController::class, 'storeTraveller'])->name('profile.traveller.store')->middleware('throttle:20,1');
    Route::patch('/profile/traveller/{traveller}', [ProfileController::class, 'updateTraveller'])->name('profile.traveller.update')->middleware('throttle:20,1');
    Route::delete('/profile/traveller/{traveller}', [ProfileController::class, 'deleteTraveller'])->name('profile.traveller.destroy')->middleware('throttle:20,1');
    Route::post('/profile/logout-other-devices', [ProfileController::class, 'logoutOtherDevices'])->name('profile.logout-other-devices')->middleware('throttle:5,1');

    // Real accounts can still spam reviews; keep it well below anything a
    // genuine user would ever hit.
    Route::post('/packages/{slug}/reviews', [FrontendController::class, 'storeReview'])->name('package.reviews.store')->middleware('throttle:5,1');

    // Authenticated users download directly — no lead capture needed
    Route::get('/packages/{slug}/download-itinerary', [FrontendController::class, 'downloadItinerary'])->name('package.download')->middleware('throttle:20,1');
});

// Guest itinerary download — collects lead, then streams PDF (rate-limited to 5/min per IP)
Route::post('/packages/{slug}/download-itinerary-guest', [FrontendController::class, 'guestDownloadItinerary'])
    ->name('package.download.guest')
    ->middleware('throttle:5,1');

require __DIR__.'/auth.php';

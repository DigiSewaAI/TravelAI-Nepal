<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrekController;               // Frontend Trek details
use App\Http\Controllers\TrekBookingController;        // Booking system
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\Agency\Auth\LoginController;
use App\Http\Controllers\Agency\Auth\RegisterController;
use App\Http\Controllers\Agency\DashboardController;
use App\Http\Controllers\Agency\TrekController as AgencyTrekController;
use App\Http\Controllers\Agency\BookingController;
use App\Http\Controllers\Agency\AgencyController;
use App\Http\Controllers\PublicTrekController;
use App\Http\Controllers\PageController;


// QR Code generation (requires SimpleSoftwareIO\QrCode)
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Booking;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/features', [PageController::class, 'features'])->name('pages.features');
Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('pages.how-it-works');
Route::get('/agencies', [PageController::class, 'agencies'])->name('pages.agencies');

Route::get('/treks', [PublicTrekController::class, 'index'])->name('treks.index');

// Home page (dynamic data from HomeController)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Public trek details page (view details from homepage)
Route::get('/trek/{trek}', [TrekController::class, 'show'])->name('trek.show');

// Public booking routes (no authentication required)
Route::get('/trek/{trek}/book', [TrekBookingController::class, 'create'])->name('trek.book');
Route::post('/trek/{trek}/book', [TrekBookingController::class, 'store']);
Route::get('/booking/confirmation/{booking}', [TrekBookingController::class, 'confirmation'])->name('booking.confirmation');

// QR Code image generation (used in agency booking list modal)
Route::get('/booking/qr/{booking}', function ($id) {
    $booking = Booking::findOrFail($id);
    return response(QrCode::size(300)->generate(route('scan.checkin', $booking->id)))
           ->header('Content-Type', 'image/svg+xml');
})->name('booking.qr');

// QR Check-in Routes (for scanning passport)
Route::get('/scan/{booking}', [CheckinController::class, 'show'])->name('scan.checkin');
Route::post('/scan/{booking}', [CheckinController::class, 'checkin']);

// Agency Authentication & Dashboard
Route::prefix('agency')->name('agency.')->group(function () {
    // Guest routes (not logged in as agency)
    Route::middleware('guest:agency')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
        Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [RegisterController::class, 'register']);
    });

    // Authenticated agency routes
    Route::middleware('auth:agency')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    // Treks Management
    Route::resource('treks', AgencyTrekController::class);
    
    // Bookings Management
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::match(['put', 'patch'], 'bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');
    
    // ========== 🆕 SUPER ADMIN को लागि Agency Management ==========
    // यी रूटहरू super admin ले मात्र प्रयोग गर्न सक्छन्
    Route::prefix('agencies')->name('agencies.')->group(function () {
    Route::get('/', [AgencyController::class, 'index'])->name('index');
    Route::get('/create', [AgencyController::class, 'create'])->name('create');
    Route::post('/', [AgencyController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [AgencyController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AgencyController::class, 'update'])->name('update');
    Route::delete('/{id}', [AgencyController::class, 'destroy'])->name('destroy');
    Route::patch('/{id}/toggle-status', [AgencyController::class, 'toggleStatus'])->name('toggle-status');
    Route::get('/{id}', [AgencyController::class, 'show'])->name('show');  // ✅ थप्नुहोस्
});
    // ============================================================
    
    // ========== 🆕 Reports (रिपोर्ट डाउनलोड) ==========
    Route::get('/reports/bookings', [DashboardController::class, 'exportBookings'])->name('reports.bookings');
    // =================================================
});
});

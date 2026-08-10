<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrekController;               // Frontend Trek details (LEGACY)
use App\Http\Controllers\TrekBookingController;        // LEGACY Booking system
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\Agency\Auth\LoginController;
use App\Http\Controllers\Agency\Auth\RegisterController;
use App\Http\Controllers\Agency\DashboardController;
use App\Http\Controllers\Agency\TrekController as AgencyTrekController;
use App\Http\Controllers\Agency\BookingController;
use App\Http\Controllers\Agency\AgencyController;
use App\Http\Controllers\PublicTrekController;
use App\Http\Controllers\PageController;

// ✅ Phase 7 – New Public Controllers
use App\Http\Controllers\Public\ServiceController;
use App\Http\Controllers\Public\BookingController as PublicBookingController;

// ✅ Phase 6 – Provider Dashboard Controllers
use App\Http\Controllers\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Provider\ProfileController as ProviderProfileController;
use App\Http\Controllers\Provider\ServiceController as ProviderServiceController;
use App\Http\Controllers\Provider\BookingController as ProviderBookingController;

// ✅ Phase 8 – Provider Subscription & Verification
use App\Http\Controllers\Provider\SubscriptionController;
use App\Http\Controllers\Provider\VerificationController;

// ✅ Phase 8 – Admin Controllers
use App\Http\Controllers\Admin\ProviderController as AdminProviderController;

// QR Code generation (requires SimpleSoftwareIO\QrCode)
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Booking;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ========================
// 1. PUBLIC PAGES & TREKS (LEGACY)
// ========================
Route::get('/features', [PageController::class, 'features'])->name('pages.features');
Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('pages.how-it-works');
Route::get('/agencies', [PageController::class, 'agencies'])->name('pages.agencies');
Route::get('/treks', [PublicTrekController::class, 'index'])->name('treks.index');
Route::get('/', [HomeController::class, 'index'])->name('home');

// Public trek details page (view details from homepage) - LEGACY
Route::get('/trek/{trek}', [TrekController::class, 'show'])->name('trek.show');

// Public booking routes (no authentication required) - LEGACY
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

// =============================================
// 2. PHASE 7 – PUBLIC MARKETPLACE (NEW)
// =============================================
Route::prefix('explore')->name('public.')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/category/{slug}', [ServiceController::class, 'category'])->name('services.category');
    Route::get('/service/{slug}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('/service/{slug}/book', [PublicBookingController::class, 'create'])->name('services.book');
    Route::post('/service/{slug}/book', [PublicBookingController::class, 'store']);
});

// Provider profile route
Route::get('/provider/{slug}', [ServiceController::class, 'providerProfile'])->name('public.providers.show');

// New booking confirmation for services (URI changed to avoid conflict)
Route::get('/service/confirmation/{booking}', [PublicBookingController::class, 'confirmation'])
    ->name('public.booking.confirmation');

// =======================================
// 3. NEW AUTHENTICATION ROUTES (User Guard)
// =======================================
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

// Password reset routes (optional – uncomment if you have views)
// Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
// Route::post('password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
// Route::get('password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
// Route::post('password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// =======================================
// 4. AGENCY ROUTES (Legacy – still active)
// =======================================
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
        Route::prefix('agencies')->name('agencies.')->group(function () {
            Route::get('/', [AgencyController::class, 'index'])->name('index');
            Route::get('/create', [AgencyController::class, 'create'])->name('create');
            Route::post('/', [AgencyController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AgencyController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AgencyController::class, 'update'])->name('update');
            Route::delete('/{id}', [AgencyController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle-status', [AgencyController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{id}', [AgencyController::class, 'show'])->name('show');
        });
        // ============================================================

        // ========== 🆕 Reports (रिपोर्ट डाउनलोड) ==========
        Route::get('/reports/bookings', [DashboardController::class, 'exportBookings'])->name('reports.bookings');
        // =================================================
    });
});

// =======================================
// 5. PROVIDER DASHBOARD ROUTES (NEW – Phase 6 & 8)
// =======================================
Route::middleware(['auth'])->prefix('provider')->name('provider.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProviderProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProviderProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProviderProfileController::class, 'update'])->name('profile.update');

    // Services
    Route::resource('services', ProviderServiceController::class);

    // Bookings
    Route::get('/bookings', [ProviderBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [ProviderBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/status', [ProviderBookingController::class, 'updateStatus'])->name('bookings.updateStatus');

    // Subscriptions (Phase 8)
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscriptions.upgrade');
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    // Verification (Phase 8)
    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::post('/verification', [VerificationController::class, 'store'])->name('verification.store');
    Route::delete('/verification/{document}', [VerificationController::class, 'destroy'])->name('verification.destroy');
});

// =======================================
// 6. ADMIN ROUTES (Super Admin – Phase 8)
// =======================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/providers', [AdminProviderController::class, 'index'])->name('providers.index');
    Route::get('/providers/{provider}', [AdminProviderController::class, 'show'])->name('providers.show');
    Route::patch('/providers/{provider}/verify', [AdminProviderController::class, 'verify'])->name('providers.verify');
    Route::patch('/providers/{provider}/toggle', [AdminProviderController::class, 'toggleActive'])->name('providers.toggle');
    Route::delete('/providers/{provider}', [AdminProviderController::class, 'destroy'])->name('providers.destroy');
});
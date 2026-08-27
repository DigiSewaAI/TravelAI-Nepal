<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

// ✅ Phase 7 – New Public Controllers
use App\Http\Controllers\Public\ServiceController;
use App\Http\Controllers\Public\BookingController as PublicBookingController;

// ✅ Phase 6 – Provider Dashboard Controllers
use App\Http\Controllers\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Provider\ProfileController as ProviderProfileController;
use App\Http\Controllers\Provider\ServiceController as ProviderServiceController;
use App\Http\Controllers\Provider\BookingController as ProviderBookingController;
use App\Http\Controllers\Public\ProviderController;

// ✅ Phase 8 – Provider Subscription & Verification
use App\Http\Controllers\Provider\SubscriptionController;
use App\Http\Controllers\Provider\VerificationController;

// ✅ Phase 9 – Payment Controllers
use App\Http\Controllers\Provider\PaymentController;
use App\Http\Controllers\WebhookController;

// ✅ Phase 8 – Admin Controllers
use App\Http\Controllers\Admin\ProviderController as AdminProviderController;

// QR Code generation (requires SimpleSoftwareIO\QrCode)
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Booking;
use App\Http\Controllers\SitemapController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
// ========================
// 1. PUBLIC PAGES
// ========================
Route::get('/features', [PageController::class, 'features'])->name('pages.features');
Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('pages.how-it-works');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pages.pricing');
// Footer Pages (Company & Legal)
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/careers', [PageController::class, 'careers'])->name('pages.careers');
Route::get('/press', [PageController::class, 'press'])->name('pages.press');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/gdpr', [PageController::class, 'gdpr'])->name('pages.gdpr');
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);


// =============================================
// 2. PUBLIC MARKETPLACE (Phase 7)
// =============================================
Route::prefix('explore')->name('public.')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/category/{slug}', [ServiceController::class, 'category'])->name('services.category');
    Route::get('/service/{slug}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('/service/{slug}/book', [PublicBookingController::class, 'create'])->name('services.book');
    Route::post('/service/{slug}/book', [PublicBookingController::class, 'store']);
});

// =============================================
// 2.5. PROVIDER DIRECTORY (Phase 12) – बाहिर
// =============================================
Route::get('/providers', [App\Http\Controllers\Public\ProviderController::class, 'index'])->name('public.providers.index');
Route::get('/providers/{provider:slug}', [App\Http\Controllers\Public\ProviderController::class, 'show'])->name('public.providers.show');

// Service booking confirmation
Route::get('/service/confirmation/{booking}', [PublicBookingController::class, 'confirmation'])
    ->name('public.booking.confirmation');


// =======================================
// 3. AUTH ROUTES (User Guard)
// =======================================
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

// =======================================
// 4. PROVIDER DASHBOARD ROUTES
// =======================================
Route::prefix('provider')->name('provider.')->group(function () {
    Route::get('/dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProviderProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProviderProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProviderProfileController::class, 'update'])->name('profile.update');

    Route::resource('services', ProviderServiceController::class);

    Route::get('/bookings', [ProviderBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [ProviderBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/status', [ProviderBookingController::class, 'updateStatus'])->name('bookings.updateStatus');

    // Subscriptions (Phase 8)
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::post('/subscriptions/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscriptions.upgrade');
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    // Verification (Phase 8)
    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::post('/verification', [VerificationController::class, 'store'])->name('verification.store');
    Route::delete('/verification/{document}', [VerificationController::class, 'destroy'])->name('verification.destroy');

    // Payment routes (Phase 9)
    Route::get('/payments', [PaymentController::class, 'history'])->name('payments.index');
    Route::get('/payments/{id}', [PaymentController::class, 'showPayment'])->name('payments.detail');
    Route::get('/payments/subscription/{subscription}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/subscription/{subscription}', [PaymentController::class, 'createPayment'])->name('payments.create');
    Route::get('/payments/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');

    // Analytics (Phase 11)
    Route::get('/analytics', [App\Http\Controllers\Provider\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/export', [App\Http\Controllers\Provider\AnalyticsController::class, 'export'])->name('analytics.export');

        Route::get('/checkins', [App\Http\Controllers\Provider\CheckinController::class, 'index'])->name('checkins.index');
    Route::get('/checkins/{scan}', [App\Http\Controllers\Provider\CheckinController::class, 'show'])->name('checkins.show');

        // Provider Invoices
    Route::get('/invoices', [App\Http\Controllers\Provider\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [App\Http\Controllers\Provider\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [App\Http\Controllers\Provider\InvoiceController::class, 'download'])->name('invoices.download');
    Route::resource('staff', \App\Http\Controllers\Provider\StaffController::class)->except(['show']);

    // AI Quotation
Route::get('/quotation/create', [App\Http\Controllers\Provider\QuotationController::class, 'create'])->name('quotation.create');
Route::post('/quotation/generate', [App\Http\Controllers\Provider\QuotationController::class, 'generate'])->name('quotation.generate');
});
// Provider profile page (old, but keep for now)
Route::get('/provider/{slug}', [ServiceController::class, 'providerProfile'])->name('public.provider.profile');

// =======================================
// 5. ADMIN ROUTES (Super Admin)
// =======================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Providers
    Route::get('/providers', [AdminProviderController::class, 'index'])->name('providers.index');
    Route::get('/providers/{provider}', [AdminProviderController::class, 'show'])->name('providers.show');
    Route::patch('/providers/{provider}/verify', [AdminProviderController::class, 'verify'])->name('providers.verify');
    Route::patch('/providers/{provider}/toggle', [AdminProviderController::class, 'toggleActive'])->name('providers.toggle');
    Route::delete('/providers/{provider}', [AdminProviderController::class, 'destroy'])->name('providers.destroy');

    // Users
    Route::resource('users', App\Http\Controllers\Admin\UserController::class)->except(['show']);
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');

    // Services
    Route::get('/services', [App\Http\Controllers\Admin\ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [App\Http\Controllers\Admin\ServiceController::class, 'show'])->name('services.show');
    Route::post('/services/{service}/toggle', [App\Http\Controllers\Admin\ServiceController::class, 'toggleStatus'])->name('services.toggle');
    Route::delete('/services/{service}', [App\Http\Controllers\Admin\ServiceController::class, 'destroy'])->name('services.destroy');

    // Bookings
    Route::get('/bookings', [App\Http\Controllers\Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [App\Http\Controllers\Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/status', [App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])->name('bookings.updateStatus');
    Route::delete('/bookings/{booking}', [App\Http\Controllers\Admin\BookingController::class, 'destroy'])->name('bookings.destroy');

    // Subscriptions
    Route::get('/subscriptions', [App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{subscription}', [App\Http\Controllers\Admin\SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/subscriptions/{subscription}/status', [App\Http\Controllers\Admin\SubscriptionController::class, 'updateStatus'])->name('subscriptions.updateStatus');
    Route::delete('/subscriptions/{subscription}', [App\Http\Controllers\Admin\SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    // Payments
    Route::get('/payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/refund', [App\Http\Controllers\Admin\PaymentController::class, 'refund'])->name('payments.refund');
    Route::delete('/payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'destroy'])->name('payments.destroy');

    // Reports
    Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/bookings', [App\Http\Controllers\Admin\ReportController::class, 'bookings'])->name('reports.bookings');
    Route::get('/reports/payments', [App\Http\Controllers\Admin\ReportController::class, 'payments'])->name('reports.payments');
    Route::get('/reports/providers', [App\Http\Controllers\Admin\ReportController::class, 'providers'])->name('reports.providers');

    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // Reviews (Phase 10)
    Route::get('/reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'show'])->name('reviews.show');
    Route::post('/reviews/{review}/approve', [App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('/reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Analytics (Phase 11)
    Route::get('/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');

        // Admin Invoices
    Route::get('/invoices', [App\Http\Controllers\Admin\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [App\Http\Controllers\Admin\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [App\Http\Controllers\Admin\InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('/invoices/{invoice}/status', [App\Http\Controllers\Admin\InvoiceController::class, 'updateStatus'])->name('invoices.update-status');
    
    // =======================================
// ADMIN – Route Management (Phase 5)
// =======================================
Route::resource('routes', \App\Http\Controllers\Admin\RouteController::class);
Route::resource('waypoints', \App\Http\Controllers\Admin\WaypointController::class);
Route::resource('segments', \App\Http\Controllers\Admin\SegmentController::class);
Route::resource('route-costs', \App\Http\Controllers\Admin\RouteCostController::class);

});

// =======================================
// 6. TRAVELER ROUTES (Phase 10)
// =======================================
Route::middleware(['auth'])->prefix('traveler')->name('traveler.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Traveler\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reviews/create/{booking}', [App\Http\Controllers\Traveler\ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews/store/{booking}', [App\Http\Controllers\Traveler\ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/bookings/{booking}', [App\Http\Controllers\Traveler\BookingController::class, 'show'])->name('bookings.show');
    
    // 🔥 Invoice Download Route (NEW)
    Route::get('/bookings/{booking}/invoice', [App\Http\Controllers\Traveler\BookingController::class, 'downloadInvoice'])
        ->name('bookings.invoice');
});
// =======================================
// 7. WEBHOOK ROUTES
// =======================================
Route::post('/webhook/stripe', [WebhookController::class, 'stripe'])->name('webhook.stripe');

// Redirect old agencies URL to new providers page
Route::get('/agencies', function () {
    return redirect()->route('public.providers.index', 301);
});

// =======================================
// 8. QR CODE & CHECK-IN ROUTES (still needed for both legacy and new bookings)
// =======================================
// QR Code image generation
Route::get('/booking/qr/{booking}', function ($id) {
    $booking = Booking::findOrFail($id);
    return response(QrCode::size(300)->generate(route('scan.checkin', $booking->id)))
           ->header('Content-Type', 'image/svg+xml');
})->name('booking.qr');

// QR Check-in Routes
Route::get('/scan/{booking}', [CheckinController::class, 'show'])->name('scan.checkin');
Route::post('/scan/{booking}', [CheckinController::class, 'checkin']);

// =======================================
// CURRENCY SWITCH (Multi-Currency Display)
// =======================================
Route::get('/currency/switch', function (Illuminate\Http\Request $request) {
    $currency = $request->input('currency', 'USD');
    $allowed = ['USD', 'NPR'];
    
    if (in_array($currency, $allowed)) {
        session(['display_currency' => $currency]);
    }
    
    return redirect()->back();
})->name('currency.switch');

// =======================================
// LANGUAGE SWITCH (Multi-Language Display)
// =======================================
Route::get('/lang/{locale}', function ($locale) {
    $allowed = ['en', 'hi', 'zh', 'np'];
    if (in_array($locale, $allowed)) {
        session(['locale' => $locale]);
        Log::info('🔍 [Language Switch] Set session locale', ['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

// ✅ Planner Route – web middleware को session पाउँछ
Route::post('/api/planner/generate', [App\Http\Controllers\Api\PlannerController::class, 'generate']);

Route::get('/test-lang', function () {
    app()->setLocale('hi');
    return __('cost.daily_food_budget');
});
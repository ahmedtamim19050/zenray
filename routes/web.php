<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Customer;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Support\AdminContent;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

Route::view('products', 'pages.equipment')->name('products.index');
Route::view('services', 'pages.services')->name('services.index');
Route::view('about', 'pages.about')->name('about');
Route::get('portfolio', [ProjectController::class, 'index'])->name('portfolio.index');
Route::get('portfolio/{project:slug}', [ProjectController::class, 'show'])->name('portfolio.show');

Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::view('privacy-policy', 'pages.privacy')->name('privacy');
Route::view('terms-and-conditions', 'pages.terms')->name('terms');
Route::view('image-credits', 'pages.credits')->name('credits');

Route::view('request-a-quote', 'pages.enquiry', ['type' => 'quote'])->name('quote');
Route::view('contact', 'pages.enquiry', ['type' => 'contact'])->name('contact');

Route::post('enquiries', [EnquiryController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('enquiries.store');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');

    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('account/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('account/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('account/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    /*
    | Customer dashboard
    */
    Route::get('dashboard', Customer\DashboardController::class)->name('dashboard');
    Route::get('dashboard/enquiries', [Customer\EnquiryController::class, 'index'])->name('dashboard.enquiries.index');
    Route::get('dashboard/enquiries/new', [Customer\EnquiryController::class, 'create'])->name('dashboard.enquiries.create');
    Route::get('dashboard/enquiries/{enquiry}', [Customer\EnquiryController::class, 'show'])->name('dashboard.enquiries.show');

    /*
    | Admin dashboard
    */
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::get('enquiries', [Admin\EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('enquiries/{enquiry}', [Admin\EnquiryController::class, 'show'])->name('enquiries.show');
        Route::patch('enquiries/{enquiry}', [Admin\EnquiryController::class, 'update'])->name('enquiries.update');
        Route::get('customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [Admin\CustomerController::class, 'show'])->name('customers.show');
        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');

        Route::resource('posts', Admin\PostController::class)->except('show');

        // Sales: quotations, invoices, payments, inventory and reports
        Route::get('quotes/{quote}/print', [Admin\QuoteController::class, 'print'])->name('quotes.print');
        Route::patch('quotes/{quote}/status', [Admin\QuoteController::class, 'status'])->name('quotes.status');
        Route::post('quotes/{quote}/convert', [Admin\QuoteController::class, 'convert'])->name('quotes.convert');
        Route::post('quotes/{quote}/duplicate', [Admin\QuoteController::class, 'duplicate'])->name('quotes.duplicate');
        Route::resource('quotes', Admin\QuoteController::class);

        Route::get('invoices/export', [Admin\InvoiceController::class, 'export'])->name('invoices.export');
        Route::get('invoices/{invoice}/print', [Admin\InvoiceController::class, 'print'])->name('invoices.print');
        Route::post('invoices/{invoice}/issue', [Admin\InvoiceController::class, 'issue'])->name('invoices.issue');
        Route::post('invoices/{invoice}/void', [Admin\InvoiceController::class, 'void'])->name('invoices.void');
        Route::post('invoices/{invoice}/payments', [Admin\PaymentController::class, 'store'])->name('invoices.payments.store');
        Route::delete('invoices/{invoice}/payments/{payment}', [Admin\PaymentController::class, 'destroy'])->name('invoices.payments.destroy');
        Route::resource('invoices', Admin\InvoiceController::class);

        Route::get('inventory/export', [Admin\InventoryController::class, 'export'])->name('inventory.export');
        Route::post('inventory/{inventory}/adjust', [Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::resource('inventory', Admin\InventoryController::class);

        Route::get('reports', Admin\ReportController::class)->name('reports');

        // Website content (hero slides, products, services, gallery…), see App\Support\AdminContent.
        Route::prefix('content/{resource}')->name('content.')
            ->whereIn('resource', array_keys(AdminContent::resources()))
            ->controller(Admin\ContentController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{id}/edit', 'edit')->whereNumber('id')->name('edit');
                Route::put('{id}', 'update')->whereNumber('id')->name('update');
                Route::post('{id}/move', 'move')->whereNumber('id')->name('move');
                Route::delete('{id}', 'destroy')->whereNumber('id')->name('destroy');
            });
    });
});

<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\ClientCategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HostingServiceController;
use App\Http\Controllers\MarketingPartnerController;
use App\Http\Controllers\PaySalaryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\SslCertificateController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Client routes
    Route::resource('clients', ClientController::class);

    // Domain routes
    Route::resource('domains', DomainController::class);
    Route::patch('/domains/{domain}/approve-level1', [DomainController::class, 'approvePaymentLevel1'])
        ->name('domains.approve-level1');
    Route::patch('/domains/{domain}/approve-level2', [DomainController::class, 'approvePaymentLevel2'])
        ->name('domains.approve-level2');

    // SSL Certificate routes
    Route::resource('ssl-certificates', SslCertificateController::class);
    Route::patch('/ssl-certificates/{sslCertificate}/approve-level1', [SslCertificateController::class, 'approvePaymentLevel1'])
        ->name('ssl-certificates.approve-level1');
    Route::patch('/ssl-certificates/{sslCertificate}/approve-level2', [SslCertificateController::class, 'approvePaymentLevel2'])
        ->name('ssl-certificates.approve-level2');

    // Hosting Service routes
    Route::resource('hosting-services', HostingServiceController::class);
    Route::patch('/hosting-services/{hostingService}/approve-level1', [HostingServiceController::class, 'approvePaymentLevel1'])
        ->name('hosting-services.approve-level1');
    Route::patch('/hosting-services/{hostingService}/approve-level2', [HostingServiceController::class, 'approvePaymentLevel2'])
        ->name('hosting-services.approve-level2');

    // Bill routes
    Route::resource('bills', BillController::class);
    Route::patch('/bills/{bill}/approve', [BillController::class, 'approve'])
        ->name('bills.approve');
    Route::patch('/bills/{bill}/payment', [BillController::class, 'updatePayment'])
        ->name('bills.update-payment');

    // User Management routes
    Route::resource('user-management', UserManagementController::class);
    Route::patch('/user-management/{userManagement}/toggle-status', [UserManagementController::class, 'toggleStatus'])
        ->name('user-management.toggle-status');

    // Employee routes
    Route::resource('employees', EmployeeController::class);

    // Payroll routes
    Route::get('/payroll', [PaySalaryController::class, 'index'])->name('payroll.index');
    Route::post('/payroll', [PaySalaryController::class, 'store'])->name('payroll.store');
    Route::put('/payroll/{paySalary}', [PaySalaryController::class, 'update'])->name('payroll.update');
    Route::delete('/payroll/{paySalary}', [PaySalaryController::class, 'destroy'])->name('payroll.destroy');
    Route::get('/payroll/report', [PaySalaryController::class, 'report'])->name('payroll.report');

    // Settings routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::prefix('client-categories')->name('client-categories.')->group(function () {
            Route::get('/', [ClientCategoryController::class, 'index'])->name('index');
            Route::get('/create', [ClientCategoryController::class, 'create'])->name('create');
            Route::post('/', [ClientCategoryController::class, 'store'])->name('store');
            Route::get('/{clientCategory}/edit', [ClientCategoryController::class, 'edit'])->name('edit');
            Route::put('/{clientCategory}', [ClientCategoryController::class, 'update'])->name('update');
            Route::delete('/{clientCategory}', [ClientCategoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('marketing-partners')->name('marketing-partners.')->group(function () {
            Route::get('/', [MarketingPartnerController::class, 'index'])->name('index');
            Route::get('/create', [MarketingPartnerController::class, 'create'])->name('create');
            Route::post('/', [MarketingPartnerController::class, 'store'])->name('store');
            Route::get('/{marketingPartner}/edit', [MarketingPartnerController::class, 'edit'])->name('edit');
            Route::put('/{marketingPartner}', [MarketingPartnerController::class, 'update'])->name('update');
            Route::delete('/{marketingPartner}', [MarketingPartnerController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('providers')->name('providers.')->group(function () {
            Route::get('/', [ProviderController::class, 'index'])->name('index');
            Route::get('/create', [ProviderController::class, 'create'])->name('create');
            Route::post('/', [ProviderController::class, 'store'])->name('store');
            Route::get('/{provider}/edit', [ProviderController::class, 'edit'])->name('edit');
            Route::put('/{provider}', [ProviderController::class, 'update'])->name('update');
            Route::delete('/{provider}', [ProviderController::class, 'destroy'])->name('destroy');
        });
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

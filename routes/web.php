<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\SslCertificateController;
use App\Http\Controllers\HostingServiceController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Foundation\Application;
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
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
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
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

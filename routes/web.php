<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AtlController;
use App\Http\Controllers\AuthDemoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoadingTicketController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\HaulerManagementController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\Auth\VerifyCodeController;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\NotificationController;

// The login page is the index. Signed-in users are sent to the dashboard automatically.
Route::redirect('/', '/login');

// Offline support: reachable without signing in so the page can check its connection.
Route::get('/pwa/ping', [PwaController::class, 'ping'])->name('pwa.ping');

// Registration: request an account, verify the email, wait for admin approval.
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
    Route::get('/register/verify', [RegistrationController::class, 'verify'])->name('register.verify');
    Route::post('/register/verify', [RegistrationController::class, 'confirm'])->middleware('throttle:10,1')->name('register.confirm');
    Route::post('/register/verify/resend', [RegistrationController::class, 'resend'])->middleware('throttle:5,1')->name('register.resend');
    Route::get('/register/pending', [RegistrationController::class, 'pending'])->name('register.pending');
});

Route::middleware('auth')->group(function () {
    Route::get('/pwa/csrf', [PwaController::class, 'csrf'])->name('pwa.csrf');
    Route::get('/pwa/urls', [PwaController::class, 'urls'])->name('pwa.urls');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/notifications/read', [NotificationController::class, 'readAll'])->name('notifications.read');

    // Authority to Load
    Route::get('/atl', [AtlController::class, 'index'])->name('atl.index');
    Route::get('/my-atl', [AtlController::class, 'mine'])->name('atl.mine');
    Route::get('/drafts', [AtlController::class, 'drafts'])->name('atl.drafts');
    Route::get('/returned', [AtlController::class, 'returned'])->name('atl.returned');
    Route::get('/atl/create', [AtlController::class, 'create'])->name('atl.create');
    Route::post('/atl', [AtlController::class, 'store'])->name('atl.store');
    Route::get('/atl/{id}', [AtlController::class, 'show'])->whereNumber('id')->name('atl.show');
    Route::post('/atl/{id}/approve', [AtlController::class, 'approve'])->whereNumber('id')->name('atl.approve');
    Route::post('/atl/{id}/decline', [AtlController::class, 'decline'])->whereNumber('id')->name('atl.decline');
    Route::get('/approvals', [OperationsController::class, 'approvals'])->name('approvals.index');
    Route::get('/approvals/{id}/allocate', [OperationsController::class, 'allocate'])->where('id', '[A-Za-z0-9\-]+')->name('approvals.allocate');

    // Loading
    Route::get('/loading', [LoadingTicketController::class, 'index'])->name('loading.index');
    Route::get('/loading-tickets/create/{atl}', [LoadingTicketController::class, 'create'])->whereNumber('atl')->name('loading-tickets.create');
    Route::post('/loading-tickets', [LoadingTicketController::class, 'store'])->name('loading-tickets.store');

    // Delivery receipt and withdrawal certificate (issued together)
    Route::get('/documents', [ReleaseController::class, 'index'])->name('documents.index');
    Route::get('/releases/create/{atl}', [ReleaseController::class, 'create'])->whereNumber('atl')->name('releases.create');
    Route::post('/releases', [ReleaseController::class, 'store'])->name('releases.store');

    // Master data
    Route::get('/hauler-management', [HaulerManagementController::class, 'index'])->name('haulers.index');
    // Old URLs still work and open the matching tab.
    Route::redirect('/trucks', '/hauler-management?tab=trucks')->name('trucks.index');
    Route::redirect('/drivers', '/hauler-management?tab=drivers')->name('drivers.index');
    Route::redirect('/haulers', '/hauler-management');
    Route::get('/products', [ManagementController::class, 'products'])->name('products.index');

    // Admin / IT
    Route::get('/admin/users', [ManagementController::class, 'users'])->name('users.index');
    Route::post('/admin/users/{id}/approve', [ManagementController::class, 'approveUser'])->whereNumber('id')->name('users.approve');
    Route::post('/admin/users/{id}/reject', [ManagementController::class, 'rejectUser'])->whereNumber('id')->name('users.reject');
    Route::post('/admin/users/{id}/toggle', [ManagementController::class, 'toggleUser'])->whereNumber('id')->name('users.toggle');
    Route::post('/admin/users/{id}/role', [ManagementController::class, 'changeRole'])->whereNumber('id')->name('users.role');

    // Reports, audit, settings, my account
    Route::get('/reports', [ManagementController::class, 'reports'])->name('reports.index');
    Route::get('/audit', [ManagementController::class, 'audit'])->name('audit.index');
    Route::get('/settings', [ManagementController::class, 'settings'])->name('settings.index');
    Route::get('/account', [AccountController::class, 'show'])->name('account.show');

    // DEMO ONLY: preview the pages as each role. Remove when real roles exist.
    $switchView = function (Request $request) {
        Role::setView((string) $request->input('role'));

        return redirect()->route('dashboard');
    };
    Route::post('/demo/role', $switchView)->name('demo.role');
    // Same action under a second name, in case an older header still calls it.
    Route::post('/developer/view-as', $switchView)->name('developer.view-as');

    // Profile pages that come with Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Email code verification (shown after a correct email/password, before login completes)
Route::middleware('guest')->group(function () {
    Route::get('/verify-code', [VerifyCodeController::class, 'show'])->name('verify.show');
    Route::post('/verify-code', [VerifyCodeController::class, 'check'])
        ->middleware('throttle:10,1')->name('verify.check');
    Route::post('/verify-code/resend', [VerifyCodeController::class, 'resend'])
        ->middleware('throttle:5,1')->name('verify.resend');
    Route::get('/verify-code/cancel', [VerifyCodeController::class, 'cancel'])->name('verify.cancel');
});

require __DIR__.'/auth.php';
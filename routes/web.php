<?php

use App\Http\Controllers\AtlController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoadingTicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\TruckMasterController;
use App\Http\Controllers\Auth\VerifyCodeController;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// The login page is the index. Signed-in users are sent to the dashboard automatically.
Route::redirect('/', '/login');

// Offline support: reachable without signing in so the page can check its connection.
Route::get('/pwa/ping', [PwaController::class, 'ping'])->name('pwa.ping');

Route::middleware('auth')->group(function () {
    Route::get('/pwa/csrf', [PwaController::class, 'csrf'])->name('pwa.csrf');
    Route::get('/pwa/urls', [PwaController::class, 'urls'])->name('pwa.urls');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Authority to Load
    Route::get('/atl', [AtlController::class, 'index'])->name('atl.index');
    Route::get('/atl/create', [AtlController::class, 'create'])->name('atl.create');
    Route::post('/atl', [AtlController::class, 'store'])->name('atl.store');
    Route::get('/atl/{id}', [AtlController::class, 'show'])->whereNumber('id')->name('atl.show');
    Route::post('/atl/{id}/approve', [AtlController::class, 'approve'])->whereNumber('id')->name('atl.approve');
    Route::post('/atl/{id}/decline', [AtlController::class, 'decline'])->whereNumber('id')->name('atl.decline');
    Route::get('/approvals', [AtlController::class, 'approvals'])->name('approvals.index');

    // Loading
    Route::get('/loading', [LoadingTicketController::class, 'index'])->name('loading.index');
    Route::get('/loading-tickets/create/{atl}', [LoadingTicketController::class, 'create'])->whereNumber('atl')->name('loading-tickets.create');
    Route::post('/loading-tickets', [LoadingTicketController::class, 'store'])->name('loading-tickets.store');

    // Delivery receipt and withdrawal certificate (issued together)
    Route::get('/documents', [ReleaseController::class, 'index'])->name('documents.index');
    Route::get('/releases/create/{atl}', [ReleaseController::class, 'create'])->whereNumber('atl')->name('releases.create');
    Route::post('/releases', [ReleaseController::class, 'store'])->name('releases.store');

    // Master data
    Route::get('/trucks', [TruckMasterController::class, 'index'])->name('trucks.index');

    Route::post('/developer/view-as', function (Request $request) {
    abort_unless(Role::isDeveloper(), 403);

    if (array_key_exists($request->input('role'), Role::LABELS)) {
        session(['view_as' => $request->input('role')]);
    }

    return redirect()->route('dashboard');
    })->name('developer.view-as');

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

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\VerifyCodeController;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
{
    $request->ensureIsNotRateLimited();

    // Check the credentials WITHOUT logging the user in yet
    if (! Auth::validate($request->only('email', 'password'))) {
        RateLimiter::hit($request->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    RateLimiter::clear($request->throttleKey());

    $user = User::where('email', $request->email)->first();

    // Hold the user in the session until the code is verified
    $request->session()->put('pending_user_id', $user->id);
    $request->session()->put('pending_remember', $request->boolean('remember'));

    VerifyCodeController::sendCode($user);

    return redirect()->route('verify.show');
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

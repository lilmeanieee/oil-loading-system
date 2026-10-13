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
use Throwable;

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

    // Only approved accounts continue. Pending, rejected and deactivated ones stop here
    // (this runs after the password was correct, so it does not reveal who has an account).
    if ($user->status !== User::STATUS_ACTIVE) {
        return $this->refuse($user);
    }

    // Hold the user in the session until the code is verified
    $request->session()->put('pending_user_id', $user->id);
    $request->session()->put('pending_remember', $request->boolean('remember'));

    VerifyCodeController::sendCode($user);

    return redirect()->route('verify.show');
}

    private function refuse(User $user): RedirectResponse
    {
        // Never finished step 2 of the request: send a new code and continue from there.
        if ($user->status === User::STATUS_PENDING && $user->email_verified_at === null) {
            try {
                RegistrationController::sendCode($user);
            } catch (Throwable $e) {
                report($e);

                throw ValidationException::withMessages(['email' => 'We could not send the verification email. Please try again.']);
            }

            session()->put('reg_user_id', $user->id);

            return redirect()->route('register.verify');
        }

        $message = match ($user->status) {
            User::STATUS_PENDING  => 'Your account is still waiting for administrator approval. We will email you once it is reviewed.',
            User::STATUS_REJECTED => 'Your account request was rejected.'.($user->rejection_reason ? ' Reason: '.$user->rejection_reason : ''),
            default               => 'Your account is deactivated. Please contact the administrator.',
        };

        throw ValidationException::withMessages(['email' => $message]);
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
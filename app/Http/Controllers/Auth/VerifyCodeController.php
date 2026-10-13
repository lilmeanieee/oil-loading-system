<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class VerifyCodeController extends Controller
{
    private const CODE_TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    /**
     * Call this from your login controller AFTER the email/password check passes
     * (but BEFORE logging the user in). See login-snippet.php.
     */
    public static function sendCode(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(self::key($user->id), [
            'hash'     => Hash::make($code),
            'attempts' => 0,
        ], now()->addMinutes(self::CODE_TTL_MINUTES));

        Mail::to($user->email)->send(new VerificationCodeMail($code, self::CODE_TTL_MINUTES));
    }

    public function show(Request $request)
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.verify-code', ['email' => $user->email]);
    }

    public function check(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']], [
            'code.required' => 'Please enter the 6-digit code.',
            'code.digits'   => 'Please enter the 6-digit code.',
        ]);

        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        $data = Cache::get(self::key($user->id));

        if (! $data) {
            return back()->withErrors(['code' => 'This code has expired. Please request a new one.']);
        }

        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget(self::key($user->id));
            return back()->withErrors(['code' => 'Too many attempts. Please request a new code.']);
        }

        if (! Hash::check($request->code, $data['hash'])) {
            $data['attempts']++;
            Cache::put(self::key($user->id), $data, now()->addMinutes(self::CODE_TTL_MINUTES));
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        // Success: finish the login
        Cache::forget(self::key($user->id));
        $remember = (bool) $request->session()->pull('pending_remember', false);
        $request->session()->forget('pending_user_id');

        $user->forceFill(['last_login_at' => now()])->save();
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function resend(Request $request)
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        // Simple cooldown so the resend button can't be spammed
        $cooldownKey = 'verify_resend_' . $user->id;
        if (Cache::has($cooldownKey)) {
            return back()->withErrors(['code' => 'Please wait a minute before requesting another code.']);
        }
        Cache::put($cooldownKey, true, now()->addSeconds(60));

        self::sendCode($user);

        return back()->with('status', 'A new verification code has been sent.');
    }

    public function cancel(Request $request)
    {
        if ($id = $request->session()->pull('pending_user_id')) {
            Cache::forget(self::key($id));
        }
        $request->session()->forget('pending_remember');

        return redirect()->route('login');
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('pending_user_id');

        return $id ? User::find($id) : null;
    }

    private static function key(int $userId): string
    {
        return 'email_verify_code_' . $userId;
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Notifications\NewAccountRequest;
use App\Support\Role;

/**
 * Request an account in three steps: details -> email code -> wait for an admin.
 * The account is created as "pending" and cannot sign in until an admin approves it
 * (see ManagementController::approveUser / rejectUser).
 */
class RegistrationController extends Controller
{
    private const CODE_TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255'],
            'requested' => ['required', 'in:atl_user,operations'], // nobody can request Admin
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if ($user && ! $this->canRequestAgain($user)) {
            throw ValidationException::withMessages(['email' => $this->duplicateMessage($user)]);
        }

        // A new request, or a fresh try by someone who never verified their email / was rejected before.
        $user ??= new User();
        $user->forceFill([
            'name'              => trim($data['name']),
            'email'             => $data['email'],
            'password'          => $data['password'], // hashed by the model cast
            'role'              => 'atl_user',        // the lowest role until an admin decides
            'requested_role'    => $data['requested'],
            'status'            => User::STATUS_PENDING,
            'email_verified_at' => null,
            'rejection_reason'  => null,
            'reviewed_by'       => null,
            'reviewed_at'       => null,
        ])->save();

        try {
            self::sendCode($user);
        } catch (Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'email' => 'We could not send the verification email. Please check the address and try again.',
            ]);
        }

        $request->session()->put('reg_user_id', $user->id);

        return redirect()->route('register.verify');
    }

    public function verify(Request $request)
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('register');
        }

        if ($user->email_verified_at) {
            return redirect()->route('register.pending');
        }

        return view('auth.register-verify', ['email' => $user->email]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']], [
            'code.required' => 'Please enter the 6-digit code.',
            'code.digits'   => 'Please enter the 6-digit code.',
        ]);

        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('register');
        }

        $data = Cache::get(self::key($user->id));

        if (! $data) {
            return back()->withErrors(['code' => 'This code has expired. Please request a new one.']);
        }

        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget(self::key($user->id));

            return back()->withErrors(['code' => 'Too many wrong tries. Please request a new code.']);
        }

        if (! Hash::check($request->input('code'), $data['hash'])) {
            $data['attempts']++;
            Cache::put(self::key($user->id), $data, now()->addMinutes(self::CODE_TTL_MINUTES));

            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        // Correct. The code can never be used again.
        Cache::forget(self::key($user->id));
                $user->forceFill(['email_verified_at' => now()])->save();

        $this->notifyAdmins($user);

        return redirect()->route('register.pending');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user || $user->email_verified_at) {
            return redirect()->route('register');
        }

        $cooldown = 'register_resend_' . $user->id;
        if (Cache::has($cooldown)) {
            return back()->withErrors(['code' => 'Please wait a minute before asking for another code.']);
        }
        Cache::put($cooldown, true, now()->addSeconds(self::RESEND_COOLDOWN_SECONDS));

        try {
            self::sendCode($user);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['code' => 'We could not send the email. Please try again in a moment.']);
        }

        return back()->with('status', 'A new verification code has been sent.');
    }

    public function pending(Request $request)
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('register');
        }

        if (! $user->email_verified_at) {
            return redirect()->route('register.verify');
        }

        return view('auth.register-pending', ['email' => $user->email]);
    }

    /** Also used by the login page when someone never finished the email step. */
    public static function sendCode(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(self::key($user->id), [
            'hash'     => Hash::make($code),
            'attempts' => 0,
        ], now()->addMinutes(self::CODE_TTL_MINUTES));

        Mail::to($user->email)->send(new VerificationCodeMail($code, self::CODE_TTL_MINUTES, 'register'));
    }

        /** Tell every active Admin, live and in their bell. A failure here must never block the person's request. */
    private function notifyAdmins(User $applicant): void
    {
        $label = Role::LABELS[$applicant->requested_role] ?? 'ATL User';

        $admins = User::where('status', User::STATUS_ACTIVE)
            ->whereIn('role', ['admin', 'developer'])
            ->get();

        foreach ($admins as $admin) {
            try {
                $admin->notify(new NewAccountRequest($applicant->name, $label));
            } catch (Throwable $e) {
                report($e); // for example, the Reverb server is not running
            }
        }
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('reg_user_id');

        return $id ? User::where('status', User::STATUS_PENDING)->find($id) : null;
    }

    private function canRequestAgain(User $user): bool
    {
        return $user->status === User::STATUS_REJECTED
            || ($user->status === User::STATUS_PENDING && $user->email_verified_at === null);
    }

    private function duplicateMessage(User $user): string
    {
        return $user->status === User::STATUS_PENDING
            ? 'A request for this email is already waiting for administrator approval.'
            : 'This email already has an account. Please sign in instead.';
    }

    private static function key(int $userId): string
    {
        return 'register_code_' . $userId;
    }
}
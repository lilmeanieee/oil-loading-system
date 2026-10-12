<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * DEMO ONLY. The three registration screens: request, email code, waiting for approval.
 * The backend developer replaces this with real registration, email codes and admin approval.
 */
class AuthDemoController extends Controller
{
    public function register()
    {
        return view('auth.register');
    }

    public function storeRegister(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'requested' => 'required|in:atl_user,operations', // nobody can request Admin
            'password'  => 'required|string|min:8|confirmed',
        ]);

        session(['reg_email' => $data['email']]);

        // TODO (backend): create the account as "pending", email a time-limited code.
        return redirect()->route('register.verify');
    }

    public function verify()
    {
        if (! session('reg_email')) {
            return redirect()->route('register');
        }

        return view('auth.register-verify', ['email' => session('reg_email')]);
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        // TODO (backend): check the code, its expiry, the wrong-code limit and that it was not used before.
        return redirect()->route('register.pending');
    }

    public function pending()
    {
        return view('auth.register-pending', ['email' => session('reg_email')]);
    }
}

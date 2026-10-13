<?php

namespace App\Http\Controllers;

use App\Support\Role;

class AccountController extends Controller
{
    public function show()
    {
        return view('account.show', ['role' => Role::LABELS[Role::actual()]]);
    }
}

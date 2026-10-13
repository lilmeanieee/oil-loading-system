<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->noContent();
    }
}
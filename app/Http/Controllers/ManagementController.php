<?php

namespace App\Http\Controllers;

use App\Mail\AccountApprovedMail;
use App\Mail\AccountRejectedMail;
use App\Models\User;
use App\Support\DemoData;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Admin / IT screens. Every action only redirects for now (demo). */
class ManagementController extends Controller
{
    private function allow(string $permission): void
    {
        abort_unless(Role::can($permission), 403);
    }

    public function users()
    {
        $this->allow('users.manage');

        return view('admin.users', ['users' => User::directory(), 'registrations' => User::registrationQueue()]);
    }

    public function approveUser(Request $request, int $id): RedirectResponse
    {
        $this->allow('users.manage');
        $data = $request->validate(['role' => 'required|in:atl_user,operations,admin,owner']);

        $user = User::where('status', User::STATUS_PENDING)->find($id);

        if (! $user) {
            return back()->withErrors(['request' => 'This request was already handled or no longer exists.']);
        }

        if (! $user->email_verified_at) {
            return back()->withErrors(['request' => 'The email must be verified before this account can be approved.']);
        }

        DB::transaction(function () use ($user, $data, $request) {
            $user->forceFill([
                'role'             => $data['role'],
                'status'           => User::STATUS_ACTIVE,
                'rejection_reason' => null,
                'reviewed_by'      => $request->user()->id,
                'reviewed_at'      => now(),
            ])->save();
        });

        $sent = $this->sendMail($user, new AccountApprovedMail($user->name, Role::LABELS[$data['role']]));

        return back()->with('status', $sent
            ? "{$user->name} was approved as ".Role::LABELS[$data['role']].'. An email was sent to '.$user->email.'.'
            : "{$user->name} was approved, but the email could not be sent. Please tell them they can sign in now.");
    }

    public function rejectUser(Request $request, int $id): RedirectResponse
    {
        $this->allow('users.manage');
        $data = $request->validate(['reason' => 'required|string|min:5|max:1000']);

        $user = User::where('status', User::STATUS_PENDING)->find($id);

        if (! $user) {
            return back()->withErrors(['request' => 'This request was already handled or no longer exists.']);
        }

        $reason = trim($data['reason']);

        DB::transaction(function () use ($user, $reason, $request) {
            $user->forceFill([
                'status'           => User::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'reviewed_by'      => $request->user()->id,
                'reviewed_at'      => now(),
            ])->save();
        });

        $sent = $this->sendMail($user, new AccountRejectedMail($user->name, $reason));

        return back()->with('status', $sent
            ? "The request from {$user->name} was rejected. An email with the reason was sent to {$user->email}."
            : "The request from {$user->name} was rejected, but the email could not be sent.");
    }

    /** The decision is already saved, so a mail problem must not undo it or crash the page. */
    private function sendMail(User $user, $mailable): bool
    {
        try {
            Mail::to($user->email)->send($mailable);

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

 public function toggleUser(Request $request, int $id): RedirectResponse
    {
        $this->allow('users.manage');

        $user = $this->findAccount($id);

        if (! $user) {
            return back()->withErrors(['request' => 'That account was not found.']);
        }

        if ($error = $this->cannotChange($user)) {
            return back()->withErrors(['request' => $error]);
        }

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['request' => 'You cannot deactivate your own account.']);
        }

        $deactivate = $user->status === User::STATUS_ACTIVE;

        if ($deactivate && $this->isLastAdmin($user)) {
            return back()->withErrors(['request' => 'This is the last active Admin. Make another account an Admin first.']);
        }

        $user->forceFill(['status' => $deactivate ? User::STATUS_INACTIVE : User::STATUS_ACTIVE])->save();

        return back()->with('status', $deactivate
            ? "{$user->name} was deactivated. They are signed out on their next click and cannot sign in until you activate the account again."
            : "{$user->name} was activated and can sign in again.");
    }

    public function changeRole(Request $request, int $id): RedirectResponse
    {
        $this->allow('users.manage');
        $data = $request->validate(['role' => 'required|in:atl_user,operations,admin,owner']);

        $user = $this->findAccount($id);

        if (! $user) {
            return back()->withErrors(['request' => 'That account was not found.']);
        }

        if ($error = $this->cannotChange($user)) {
            return back()->withErrors(['request' => $error]);
        }

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['request' => 'You cannot change your own role.']);
        }

        if ($user->role === $data['role']) {
            return back()->with('status', "{$user->name} already has the ".Role::LABELS[$data['role']].' role.');
        }

        if ($data['role'] !== 'admin' && $this->isLastAdmin($user)) {
            return back()->withErrors(['request' => 'This is the last active Admin. Make another account an Admin first.']);
        }

        $user->forceFill(['role' => $data['role']])->save();

        return back()->with('status', "{$user->name} is now ".Role::LABELS[$data['role']].'. It applies on their next page load.');
    }

    private function findAccount(int $id): ?User
    {
        return User::whereIn('status', [User::STATUS_ACTIVE, User::STATUS_INACTIVE])->find($id);
    }

    /** Only a Developer may touch a Developer account. */
    private function cannotChange(User $user): ?string
    {
        return ($user->role === 'developer' && ! Role::isDeveloper())
            ? 'Only a Developer can change a Developer account.'
            : null;
    }

     /** True when this is the only active Admin/Developer, so removing them would lock everyone out of admin. */
    private function isLastAdmin(User $user): bool
    {
        return $user->status === User::STATUS_ACTIVE
            && in_array($user->role, ['admin', 'developer'], true)
            && ! User::where('status', User::STATUS_ACTIVE)
                ->whereIn('role', ['admin', 'developer'])
                ->where('id', '!=', $user->id)
                ->exists();
    }


    public function products()
    {
        $this->allow('products.manage');

        return view('admin.products', ['products' => DemoData::products()]);
    }

    public function reports()
    {
        $this->allow('reports.view');

        return view('admin.reports', ['atls' => DemoData::atls(), 'activity' => DemoData::documentActivity()]);
    }

    public function audit()
    {
        $this->allow('audit.view');

        return view('admin.audit', ['events' => DemoData::audit()]);
    }

    public function settings()
    {
        $this->allow('settings.manage');

        return view('admin.settings', ['devices' => DemoData::devices()]);
    }
}
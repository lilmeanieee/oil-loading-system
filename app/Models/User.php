<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'requested_role', 'rejection_reason', 'reviewed_by', 'reviewed_at', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'reviewed_at'       => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /** Account requests waiting for an admin, oldest first. Same shape the admin screens already use. */
    public static function registrationQueue(): array
    {
        return static::where('status', self::STATUS_PENDING)
            ->oldest()
            ->get()
            ->map(fn (self $u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'requested'  => array_key_exists((string) $u->requested_role, Role::LABELS) ? $u->requested_role : 'atl_user',
                'verified'   => $u->email_verified_at !== null,
                'registered' => $u->created_at->toDateTimeString(),
            ])
            ->all();
    }

    /** Approved accounts (active or inactive) for the "All users" tab. */
    public static function directory(): array
    {
        return static::whereIn('status', [self::STATUS_ACTIVE, self::STATUS_INACTIVE])
            ->orderBy('name')
            ->get()
            ->map(fn (self $u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => array_key_exists((string) $u->role, Role::LABELS) ? $u->role : 'atl_user',
                'status'     => ucfirst($u->status),
                'last_login' => $u->last_login_at?->toDateTimeString(),
            ])
            ->all();
    }
}
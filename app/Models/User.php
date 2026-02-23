<?php

namespace App\Models;

use App\Enums\UserRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'role',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'user',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRoles::class,
        ];
    }

    /**
     * Check if the user has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRoles::ADMIN;
    }

    /**
     * Check if the user has moderator role.
     */
    public function isModerator(): bool
    {
        return $this->role === UserRoles::MODERATOR;
    }

    /**
     * Check if the user has user role.
     */
    public function isUser(): bool
    {
        return $this->role === UserRoles::USER;
    }

    /**
     * Check if the user is an admin or moderator.
     */
    public function isAdminOrModerator(): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    /**
     * Scope a query to search users by name, email, role, verification status, and creation date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $searchParams
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, array $searchParams)
    {
        $name = $searchParams['name'] ?? null;
        $email = $searchParams['email'] ?? null;
        $role = $searchParams['role'] ?? null;
        $isVerified = $searchParams['isVerified'] ?? null;
        $createdAtFrom = $searchParams['createdAtFrom'] ?? null;
        $createdAtTo = $searchParams['createdAtTo'] ?? null;

        if ($name) {
            $query->where('name', 'like', '%' . trim($name) . '%');
        }

        if ($email) {
            $query->where('email', 'like', '%' . trim($email) . '%');
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($isVerified !== null) {
            if ($isVerified === '1') {
                $query->whereNotNull('email_verified_at');
            } elseif($isVerified === '0') {
                $query->whereNull('email_verified_at');
            }
        }

        if ($createdAtFrom) {
            $query->whereDate('created_at', '>=', $createdAtFrom);
        }

        if ($createdAtTo) {
            $query->whereDate('created_at', '<=', $createdAtTo);
        }

        return $query;
    }
}

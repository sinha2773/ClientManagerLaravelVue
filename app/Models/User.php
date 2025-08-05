<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
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
        'password',
        'user_type',
        'is_active',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is an account manager
     */
    public function isAccountManager(): bool
    {
        return $this->user_type === 'account_manager';
    }

    /**
     * Check if user is an approver
     */
    public function isApprover(): bool
    {
        return $this->user_type === 'approver';
    }

    /**
     * Check if user can approve bills
     */
    public function canApproveBills(): bool
    {
        return $this->isApprover() && $this->is_active;
    }

    /**
     * Check if user can manage bills
     */
    public function canManageBills(): bool
    {
        return ($this->isAccountManager() || $this->isApprover()) && $this->is_active;
    }

    /**
     * Bills created by this user
     */
    public function createdBills(): HasMany
    {
        return $this->hasMany(Bill::class, 'created_by');
    }

    /**
     * Bills approved by this user
     */
    public function approvedBills(): HasMany
    {
        return $this->hasMany(Bill::class, 'approved_by');
    }
}

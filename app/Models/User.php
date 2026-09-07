<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'type',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'company_name',
        'company_address',
        'company_phone',
        'address',
        'created_by_contractor_id',
        'invited_at',
        'activated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'email_verified_at' => 'datetime',
            'invited_at'        => 'datetime',
            'activated_at'      => 'datetime',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isContractor(): bool
    {
        return $this->type === 'contractor';
    }

    public function isClient(): bool
    {
        return $this->type === 'client';
    }

    // A client has an active online account once they've set a password
    public function hasActivatedAccount(): bool
    {
        return $this->isClient() && !is_null($this->activated_at);
    }

    // ── Contractor relations ────────────────────────────────────────────

    // Clients this contractor has created
    public function clients()
    {
        return $this->hasMany(User::class, 'created_by_contractor_id')->where('type', 'client');
    }

    public function projectsAsContractor()
    {
        return $this->hasMany(Project::class, 'contractor_id');
    }

    // ── Client relations ─────────────────────────────────────────────────

    public function createdByContractor()
    {
        return $this->belongsTo(User::class, 'created_by_contractor_id');
    }

    public function projectsAsClient()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    // Every project this user is party to, whichever role they hold
    public function projects()
    {
        return $this->isContractor() ? $this->projectsAsContractor() : $this->projectsAsClient();
    }
}

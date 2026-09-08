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
        'phone',
        'company_name',
        'company_address',
        'company_phone',
        'address',
        'created_by_contractor_id',
        'invited_at',
        'activated_at',
        'is_demo',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'invited_at'        => 'datetime',
            'activated_at'      => 'datetime',
            'is_demo'           => 'boolean',
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

    // A client has an active online account once they've used their first login link
    public function hasActivatedAccount(): bool
    {
        return $this->isClient() && !is_null($this->activated_at);
    }

    public function loginLinks()
    {
        return $this->hasMany(LoginLink::class);
    }

    // The single public-demo contractor account. Any client/project created
    // under it, and the "Try the demo" login button, all key off this.
    public function isDemoAccount(): bool
    {
        return (bool) $this->is_demo;
    }

    // Finds the one demo contractor, creating it the first time anything
    // needs it (the "Try the demo" button, or the demo:reset command) —
    // so the feature works even before that command has ever run.
    public static function demoContractor(): self
    {
        return static::firstOrCreate(
            ['email' => 'demo@remodelpro.test'],
            [
                'type'               => 'contractor',
                'is_demo'            => true,
                'first_name'         => 'Demo',
                'last_name'          => 'Contractor',
                'phone'              => '555-0100',
                'company_name'       => 'Demo Renovations Co.',
                'company_address'    => '123 Demo Lane, Sample City, TX',
                'company_phone'      => '555-0100',
                'email_verified_at'  => now(),
            ]
        );
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

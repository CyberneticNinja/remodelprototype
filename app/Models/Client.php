<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'contractor_id',
        'first_name',
        'last_name',
        'phone',
        'address',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}

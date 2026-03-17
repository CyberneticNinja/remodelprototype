<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'contractor_id',
        'client_id',
        'title',
        'address',
    ];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    // A project is complete when all its rooms are complete
    public function getIsCompleteAttribute(): bool
    {
        if ($this->rooms->isEmpty()) {
            return false;
        }

        return $this->rooms->every(fn($room) => $room->is_complete);
    }
}

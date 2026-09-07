<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RoomPhoto extends Model
{
    protected $fillable = [
        'room_id',
        'path',
        'type', // 'before' or 'after'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}

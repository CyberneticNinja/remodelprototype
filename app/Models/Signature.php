<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $fillable = [
        'room_id',
        'stage',       // 'work_agreed' or 'completed'
        'role',        // 'contractor' or 'client'
        'signature_data', // base64 canvas image
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}

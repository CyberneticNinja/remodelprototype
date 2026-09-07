<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $fillable = [
        'room_id',
        'stage',                    // 'work_agreed' or 'completed'
        'role',                     // 'contractor' or 'client'
        'method',                   // 'online' or 'in_person'
        'signed_by_user_id',
        'signer_name_confirmation', // typed name, only set for in-person signing
        'signature_data',           // base64 canvas image
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

    public function signedByUser()
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }

    public function isOnline(): bool
    {
        return $this->method === 'online';
    }
}

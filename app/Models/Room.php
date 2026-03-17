<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'notes',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function photos()
    {
        return $this->hasMany(RoomPhoto::class);
    }

    public function beforePhotos()
    {
        return $this->hasMany(RoomPhoto::class)->where('type', 'before');
    }

    public function afterPhotos()
    {
        return $this->hasMany(RoomPhoto::class)->where('type', 'after');
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }

    // Check if a specific stage has both signatures
    public function stageComplete(string $stage): bool
    {
        $signed = $this->signatures()
            ->where('stage', $stage)
            ->pluck('role')
            ->toArray();

        return in_array('contractor', $signed) && in_array('client', $signed);
    }

    // Work agreed stage is complete
    public function getWorkAgreedCompleteAttribute(): bool
    {
        return $this->stageComplete('work_agreed');
    }

    // Completed stage is complete
    public function getIsCompleteAttribute(): bool
    {
        return $this->stageComplete('completed');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'notes',
        'scope_description',
        'estimated_cost',
        'estimated_duration_days',
        'estimate_locked_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost'      => 'decimal:2',
            'estimate_locked_at'  => 'datetime',
        ];
    }

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

    // The estimate (scope/cost/duration) can only be edited until the
    // first work_agreed signature is collected — after that it's frozen.
    public function isEstimateLocked(): bool
    {
        return !is_null($this->estimate_locked_at);
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

    // Before photos are the "here's what it looked like" snapshot that's
    // part of what gets agreed to — they freeze at the same moment the
    // estimate does (first work_agreed signature).
    public function canUploadBeforePhotos(): bool
    {
        return !$this->isEstimateLocked();
    }

    // After photos only make sense once work has actually been agreed to
    // start, and they freeze once the room is fully signed off as complete.
    public function canUploadAfterPhotos(): bool
    {
        return $this->work_agreed_complete && !$this->is_complete;
    }
}

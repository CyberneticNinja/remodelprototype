<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    // Access to a room follows access to its parent project.
    public function view(User $user, Room $room): bool
    {
        return $room->project->contractor_id === $user->id
            || $room->project->client_id === $user->id;
    }

    // Only the contractor who owns the project can create/edit rooms.
    public function create(User $user, ?Room $room = null): bool
    {
        return $user->isContractor();
    }

    public function update(User $user, Room $room): bool
    {
        return $user->isContractor()
            && $room->project->contractor_id === $user->id
            && !$room->isEstimateLocked();
    }
}

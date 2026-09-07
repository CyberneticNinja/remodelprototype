<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomPhotoController extends Controller
{
    // Upload a before/after photo — contractor-owner only, subject to the
    // room's upload window for that gallery. (Not gated by RoomPolicy@update,
    // since that ability is specifically about editing the estimate while
    // unlocked — after photos are meant to be uploadable precisely once the
    // estimate IS locked.)
    public function store(Request $request, Project $project, Room $room)
    {
        abort_if($room->project_id !== $project->id, 403);

        $user = Auth::user();
        abort_if(!$user->isContractor() || $project->contractor_id !== $user->id, 403);

        $validated = $request->validate([
            'type'  => 'required|in:before,after',
            'photo' => 'required|image|max:8192', // 8MB
        ]);

        if ($validated['type'] === 'before') {
            abort_unless($room->canUploadBeforePhotos(), 403,
                'Before photos are locked once work has been agreed on.');
        } else {
            abort_unless($room->canUploadAfterPhotos(), 403,
                'After photos can only be added once work is agreed on, and lock once the room is complete.');
        }

        $path = $request->file('photo')->store("rooms/{$room->id}/{$validated['type']}", 'public');

        $room->photos()->create([
            'type' => $validated['type'],
            'path' => $path,
        ]);

        return redirect()->route('rooms.show', [$project, $room])
            ->with('success', ucfirst($validated['type']) . ' photo uploaded.');
    }
}

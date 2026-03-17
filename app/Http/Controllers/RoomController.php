<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    // Show create room form
    public function create(Project $project)
    {
        abort_if($project->contractor_id !== Auth::guard('contractor')->id(), 403);

        return view('rooms.create', compact('project'));
    }

    // Store new room
    public function store(Request $request, Project $project)
    {
        abort_if($project->contractor_id !== Auth::guard('contractor')->id(), 403);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $project->rooms()->create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Room added successfully.');
    }

    // Show a single room
    public function show(Project $project, Room $room)
    {
        abort_if($project->contractor_id !== Auth::guard('contractor')->id(), 403);
        abort_if($room->project_id !== $project->id, 403);

        $room->load('signatures', 'beforePhotos', 'afterPhotos');

        return view('rooms.show', compact('project', 'room'));
    }
}
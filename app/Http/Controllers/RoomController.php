<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // Show create room form
    public function create(Project $project)
    {
        $this->authorize('view', $project);
        $this->authorize('create', Room::class);

        return view('rooms.create', compact('project'));
    }

    // Store new room
    public function store(Request $request, Project $project)
    {
        $this->authorize('view', $project);
        $this->authorize('create', Room::class);

        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'notes'                   => 'nullable|string',
            'scope_description'       => 'nullable|string',
            'estimated_cost'          => 'nullable|numeric|min:0',
            'estimated_duration_days' => 'nullable|integer|min:0',
        ]);

        $project->rooms()->create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Room added successfully.');
    }

    // Show a single room
    public function show(Project $project, Room $room)
    {
        $this->authorize('view', $project);
        abort_if($room->project_id !== $project->id, 403);

        $room->load('signatures.signedByUser', 'beforePhotos', 'afterPhotos');

        return view('rooms.show', compact('project', 'room'));
    }

    // Show edit form — only while the estimate isn't locked yet
    public function edit(Project $project, Room $room)
    {
        abort_if($room->project_id !== $project->id, 403);
        $this->authorize('update', $room);

        return view('rooms.edit', compact('project', 'room'));
    }

    // Update the room's notes / estimate (scope, cost, duration)
    public function update(Request $request, Project $project, Room $room)
    {
        abort_if($room->project_id !== $project->id, 403);
        $this->authorize('update', $room);

        $validated = $request->validate([
            'notes'                   => 'nullable|string',
            'scope_description'       => 'nullable|string',
            'estimated_cost'          => 'nullable|numeric|min:0',
            'estimated_duration_days' => 'nullable|integer|min:0',
        ]);

        $room->update($validated);

        return redirect()->route('rooms.show', [$project, $room])
            ->with('success', 'Room updated.');
    }
}

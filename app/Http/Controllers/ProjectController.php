<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    // Dashboard - list all projects for logged in contractor
    public function index(Request $request)
    {
        $query = Auth::guard('contractor')->user()
            ->projects()
            ->with('client', 'rooms');

        // Search by title, address or client name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $projects = $query->latest()->paginate(10);

        return view('projects.index', compact('projects'));
    }

    // Show create project form
    public function create()
    {
        $clients = Auth::guard('contractor')->user()->clients()->orderBy('first_name')->get();

        return view('projects.create', compact('clients'));
    }

    // Store new project + default room
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'address'   => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
        ]);

        $contractor = Auth::guard('contractor')->user();

        // Make sure the client belongs to this contractor
        $contractor->clients()->findOrFail($validated['client_id']);

        $project = $contractor->projects()->create($validated);

        // Always start with one default room
        Room::create([
            'project_id' => $project->id,
            'name'       => 'Bedroom',
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    // Show a single project and its rooms
    public function show(Project $project)
    {
        // Make sure this project belongs to the logged in contractor
        abort_if($project->contractor_id !== Auth::guard('contractor')->id(), 403);

        $project->load('client', 'rooms.signatures', 'rooms.beforePhotos', 'rooms.afterPhotos');

        return view('projects.show', compact('project'));
    }
}

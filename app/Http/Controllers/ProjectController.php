<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    // Dashboard — contractors see their own projects, clients see the
    // projects they're a party to (read-only).
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->projects()->with('client', 'contractor', 'rooms');

        // Search by title, address or client name (contractor only)
        if ($user->isContractor() && $request->filled('search')) {
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
        $this->authorize('create', Project::class);

        $clients = Auth::user()->clients()->orderBy('first_name')->get();

        return view('projects.create', compact('clients'));
    }

    // Store new project + default room
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'address'   => 'required|string|max:255',
            'client_id' => 'required|exists:users,id',
        ]);

        $contractor = Auth::user();

        // Make sure the client belongs to this contractor
        $contractor->clients()->findOrFail($validated['client_id']);

        $project = $contractor->projectsAsContractor()->create($validated);

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
        $this->authorize('view', $project);

        $project->load('client', 'contractor', 'rooms.signatures', 'rooms.beforePhotos', 'rooms.afterPhotos');

        return view('projects.show', compact('project'));
    }
}

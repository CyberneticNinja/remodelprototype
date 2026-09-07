<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Room;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatureController extends Controller
{
    private const STAGES = ['work_agreed', 'completed'];
    private const ROLES  = ['contractor', 'client'];

    // ── Self-service signing: the contractor signing their own role, or a
    //    client who is logged in as themselves signing online. ────────────

    public function create(Project $project, Room $room, string $stage, string $role)
    {
        $this->guardStageRole($stage, $role);
        $this->guardSelfService($project, $role);
        abort_if($room->project_id !== $project->id, 403);
        $this->guardStageOrder($room, $stage);

        if ($existing = $this->existingSignature($room, $stage, $role)) {
            return $this->alreadySignedRedirect($project, $room, $role);
        }

        return view('signatures.create', compact('project', 'room', 'stage', 'role'));
    }

    public function store(Request $request, Project $project, Room $room, string $stage, string $role)
    {
        $this->guardStageRole($stage, $role);
        $this->guardSelfService($project, $role);
        abort_if($room->project_id !== $project->id, 403);
        $this->guardStageOrder($room, $stage);

        $request->validate(['signature_data' => 'required|string']);

        $this->saveSignature($room, $stage, $role, [
            'method'            => 'online',
            'signed_by_user_id' => Auth::id(),
            'signature_data'    => $request->signature_data,
        ]);

        return redirect()->route('rooms.show', [$project, $room])
            ->with('success', ucfirst($role) . ' signature saved successfully.');
    }

    // ── In-person signing: contractor hands the device to the client. ─────
    //    Only for role=client — a contractor always signs their own role
    //    themselves, since it's already their own device/session.

    public function createInPerson(Project $project, Room $room, string $stage)
    {
        $this->guardStageRole($stage, 'client');
        $this->guardContractorOwner($project);
        abort_if($room->project_id !== $project->id, 403);
        $this->guardStageOrder($room, $stage);

        if ($this->existingSignature($room, $stage, 'client')) {
            return $this->alreadySignedRedirect($project, $room, 'client');
        }

        $role = 'client';
        return view('signatures.create-in-person', compact('project', 'room', 'stage', 'role'));
    }

    public function storeInPerson(Request $request, Project $project, Room $room, string $stage)
    {
        $this->guardStageRole($stage, 'client');
        $this->guardContractorOwner($project);
        abort_if($room->project_id !== $project->id, 403);
        $this->guardStageOrder($room, $stage);

        $validated = $request->validate([
            'signature_data'           => 'required|string',
            'signer_name_confirmation' => 'required|string|max:255',
        ]);

        $client = $project->client;
        $typed  = strtolower(trim($validated['signer_name_confirmation']));

        if ($typed !== strtolower($client->full_name) && $typed !== strtolower($client->first_name)) {
            return back()->withErrors([
                'signer_name_confirmation' => "Please type the client's name as shown: {$client->full_name}.",
            ])->withInput();
        }

        $this->saveSignature($room, $stage, 'client', [
            'method'                    => 'in_person',
            'signed_by_user_id'         => $client->id,
            'signer_name_confirmation'  => $validated['signer_name_confirmation'],
            'signature_data'            => $validated['signature_data'],
        ]);

        return redirect()->route('rooms.show', [$project, $room])
            ->with('success', 'Client signature saved successfully.');
    }

    // ── Shared helpers ──────────────────────────────────────────────────

    private function guardStageRole(string $stage, string $role): void
    {
        abort_if(!in_array($stage, self::STAGES), 404);
        abort_if(!in_array($role, self::ROLES), 404);
    }

    private function guardContractorOwner(Project $project): void
    {
        $user = Auth::user();
        abort_if(!$user->isContractor() || $project->contractor_id !== $user->id, 403);
    }

    // The authenticated user must BE the party signing: the contractor-owner
    // for role=contractor, or the assigned client (logged in) for role=client.
    private function guardSelfService(Project $project, string $role): void
    {
        $user = Auth::user();

        if ($role === 'contractor') {
            abort_if(!$user->isContractor() || $project->contractor_id !== $user->id, 403);
        } else {
            abort_if(!$user->isClient() || $project->client_id !== $user->id, 403);
        }
    }

    // Work can't be marked "completed" until both parties have agreed to it
    // first — the completed stage only opens up once work_agreed is fully signed.
    private function guardStageOrder(Room $room, string $stage): void
    {
        abort_if($stage === 'completed' && !$room->work_agreed_complete, 403,
            'Both parties must sign Work Agreed On before Completed can be signed.');
    }

    private function existingSignature(Room $room, string $stage, string $role): ?Signature
    {
        return Signature::where('room_id', $room->id)
            ->where('stage', $stage)
            ->where('role', $role)
            ->first();
    }

    private function alreadySignedRedirect(Project $project, Room $room, string $role)
    {
        return redirect()->route('rooms.show', [$project, $room])
            ->with('success', ucfirst($role) . ' has already signed this stage.');
    }

    private function saveSignature(Room $room, string $stage, string $role, array $attributes): void
    {
        Signature::updateOrCreate(
            ['room_id' => $room->id, 'stage' => $stage, 'role' => $role],
            [...$attributes, 'signed_at' => now()]
        );

        // The estimate (and before-photos gallery) freezes the moment
        // work_agreed collects its first signature.
        if ($stage === 'work_agreed' && !$room->isEstimateLocked()) {
            $room->update(['estimate_locked_at' => now()]);
        }
    }
}

<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    // Contractor-owner has full access; the assigned client can view only.
    public function view(User $user, Project $project): bool
    {
        return $project->contractor_id === $user->id
            || $project->client_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isContractor();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isContractor() && $project->contractor_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isContractor() && $project->contractor_id === $user->id;
    }
}

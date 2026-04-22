<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\User;

class FarmPolicy
{
    /**
     * Determine if the user can view any farms.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the farm.
     */
    public function view(User $user, Farm $farm): bool
    {
        // Owner has access
        if ($farm->user_id === $user->id) {
            return true;
        }

        // User has explicit access via farm_user pivot
        return $farm->users()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine if the user can create farms.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the farm.
     */
    public function update(User $user, Farm $farm): bool
    {
        // Owner can update
        if ($farm->user_id === $user->id) {
            return true;
        }

        // Editors can update
        return $farm->users()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'editor')
            ->exists();
    }

    /**
     * Determine if the user can delete the farm.
     */
    public function delete(User $user, Farm $farm): bool
    {
        // Only owner can delete
        return $farm->user_id === $user->id;
    }

    /**
     * Determine if the user can grant access to the farm.
     *
     * Rules:
     * - Owner can grant access to anyone
     * - Engineer with 'editor' access can grant access (controller validates they can only add their supervised farmers)
     */
    public function grantAccess(User $user, Farm $farm): bool
    {
        // Owner can always grant access
        if ($farm->user_id === $user->id) {
            return true;
        }

        // Engineer with editor access can grant access
        if ($user->role === 'engineer') {
            return $farm->users()
                ->where('user_id', $user->id)
                ->wherePivot('role', 'editor')
                ->exists();
        }

        return false;
    }
}

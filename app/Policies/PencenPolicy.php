<?php

namespace App\Policies;

use App\Models\Pencen;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PencenPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pencen $pencen): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
       return $user->isSuperAdmin() || $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pencen $pencen): bool
    {
       return $user->isSuperAdmin() || $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pencen $pencen): bool
    {
       return $user->isSuperAdmin() || $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pencen $pencen): bool
    {
       return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pencen $pencen): bool
    {
       return $user->isSuperAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restoreAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}

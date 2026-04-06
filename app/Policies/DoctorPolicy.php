<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class DoctorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAnyDoctor(User $user): bool
    {
        if($user->can('list_doctor')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewDoctor(User $user, User $model= null): bool
    {
        if($user->can('edit_doctor')){
            return true;
        }
        return false;
    }

    public function profileDoctor(User $user, User $model= null): bool
    {
        if($user->can('profile_doctor')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function createDoctor(User $user): bool
    {
        if($user->can('register_doctor')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function updateDoctor(User $user, User $model= null): bool
    {
        if($user->can('edit_doctor')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function deleteDoctor(User $user, User $model= null): bool
    {
        if($user->can('delete_doctor')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, User $model): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, User $model): bool
    // {
    //     //
    // }
}

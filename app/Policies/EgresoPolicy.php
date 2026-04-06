<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Egreso\Egreso;

class EgresoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('list_egreso');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('register_egreso');
    }

    public function update(User $user, Egreso $egreso): bool
    {
        return $user->hasPermissionTo('edit_egreso');
    }

    public function delete(User $user, Egreso $egreso): bool
    {
        return $user->hasPermissionTo('delete_egreso');
    }
}

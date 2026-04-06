<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ingreso\Ingreso;

class IngresoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('list_ingreso');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('register_ingreso');
    }

    public function update(User $user, Ingreso $ingreso): bool
    {
        return $user->hasPermissionTo('edit_ingreso');
    }

    public function delete(User $user, Ingreso $ingreso): bool
    {
        return $user->hasPermissionTo('delete_ingreso');
    }
}

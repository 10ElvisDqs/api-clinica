<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Seguimiento\Seguimiento;

class SeguimientoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('list_seguimiento');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('register_seguimiento');
    }

    public function update(User $user, Seguimiento $seguimiento): bool
    {
        return $user->hasPermissionTo('edit_seguimiento');
    }

    public function delete(User $user, Seguimiento $seguimiento): bool
    {
        return $user->hasPermissionTo('delete_seguimiento');
    }
}

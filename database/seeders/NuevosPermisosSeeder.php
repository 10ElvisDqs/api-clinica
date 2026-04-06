<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class NuevosPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            // Ingreso
            'list_ingreso', 'register_ingreso', 'edit_ingreso', 'delete_ingreso',
            // Egreso
            'list_egreso', 'register_egreso', 'edit_egreso', 'delete_egreso',
            // Seguimiento
            'list_seguimiento', 'register_seguimiento', 'edit_seguimiento', 'delete_seguimiento',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'api']);
        }

        // Asignar todos los permisos nuevos al rol Super-Admin (por si acaso)
        $superAdmin = Role::where('name', 'Super-Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permisos);
        }

        // Asignar al rol DOCTOR los permisos de seguimiento (vista)
        $doctor = Role::where('name', 'DOCTOR')->first();
        if ($doctor) {
            $doctor->givePermissionTo([
                'list_seguimiento', 'register_seguimiento', 'edit_seguimiento',
            ]);
        }

        // Asignar a ENFERMERO permisos de ingreso y egreso
        $enfermero = Role::where('name', 'ENFERMERO')->first();
        if ($enfermero) {
            $enfermero->givePermissionTo([
                'list_ingreso', 'register_ingreso', 'edit_ingreso',
                'list_egreso',  'register_egreso',  'edit_egreso',
                'list_seguimiento',
            ]);
        }

        $this->command->info('Nuevos permisos creados y asignados correctamente.');
    }
}

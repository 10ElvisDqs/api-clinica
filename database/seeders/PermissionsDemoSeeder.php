<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsDemoSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permisos ────────────────────────────────────────────────────────
        $permisos = [
            // Roles
            'register_rol', 'list_rol', 'edit_rol', 'delete_rol',
            // Doctores
            'register_doctor', 'list_doctor', 'edit_doctor', 'delete_doctor', 'profile_doctor',
            // Pacientes
            'register_patient', 'list_patient', 'edit_patient', 'delete_patient', 'profile_patient',
            // Staff
            'register_staff', 'list_staff', 'edit_staff', 'delete_staff',
            // Citas
            'register_appointment', 'list_appointment', 'edit_appointment', 'delete_appointment',
            // Especialidades
            'register_specialty', 'list_specialty', 'edit_specialty', 'delete_specialty',
            // Pagos
            'show_payment', 'edit_payment',
            // Actividad y calendario
            'activitie', 'calendar',
            // Reportes
            'expense_report', 'invoice_report',
            // Configuración
            'settings',
            // Ingreso
            'list_ingreso', 'register_ingreso', 'edit_ingreso', 'delete_ingreso',
            // Egreso
            'list_egreso', 'register_egreso', 'edit_egreso', 'delete_egreso',
            // Seguimiento
            'list_seguimiento', 'register_seguimiento', 'edit_seguimiento', 'delete_seguimiento',
            // Portal paciente
            'view_own_profile', 'view_own_appointments', 'view_own_history', 'view_own_seguimientos',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['guard_name' => 'api', 'name' => $permiso]);
        }

        // ── Roles y sus permisos ────────────────────────────────────────────

        // Super-Admin — sin restricciones (Gate::before lo cubre todo)
        $superAdmin = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'Super-Admin']);

        // ADMINISTRADOR — acceso total explícito
        $admin = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'ADMINISTRADOR']);
        $admin->syncPermissions($permisos);

        // RECEPCIONISTA — pacientes, citas, pagos
        $recep = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'RECEPCIONISTA']);
        $recep->syncPermissions([
            'list_patient', 'register_patient', 'edit_patient', 'profile_patient',
            'list_appointment', 'register_appointment', 'edit_appointment',
            'show_payment', 'edit_payment',
            'activitie',
            'list_seguimiento',
        ]);

        // ENFERMERO — pacientes, calendario, ingreso, egreso, seguimiento (ver)
        $enfermero = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'ENFERMERO']);
        $enfermero->syncPermissions([
            'list_patient', 'register_patient', 'edit_patient', 'profile_patient',
            'calendar', 'activitie',
            'list_ingreso', 'register_ingreso', 'edit_ingreso',
            'list_egreso', 'register_egreso', 'edit_egreso',
            'list_seguimiento',
        ]);

        // DOCTOR — pacientes (info), calendario, seguimiento, reporte
        $doctor = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'DOCTOR']);
        $doctor->syncPermissions([
            'list_patient', 'profile_patient',
            'calendar', 'activitie',
            'list_seguimiento', 'register_seguimiento', 'edit_seguimiento',
            'invoice_report',
        ]);

        // PACIENTE — solo su propia información (portal paciente)
        $paciente = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'PACIENTE']);
        $paciente->syncPermissions([
            'view_own_profile', 'view_own_appointments', 'view_own_history', 'view_own_seguimientos',
        ]);

        // ── Usuarios de prueba ──────────────────────────────────────────────
        $usuarios = [
            [
                'name'     => 'Super-Admin User',
                'surname'  => '',
                'email'    => 'dquinteros630@gmail.com',
                'password' => bcrypt('12345678'),
                'role'     => 'Super-Admin',
            ],
            [
                'name'     => 'Administrador',
                'surname'  => 'Clínica',
                'email'    => 'admin@clinica.com',
                'password' => bcrypt('12345678'),
                'role'     => 'ADMINISTRADOR',
            ],
            [
                'name'     => 'María',
                'surname'  => 'García',
                'email'    => 'recepcionista@clinica.com',
                'password' => bcrypt('12345678'),
                'role'     => 'RECEPCIONISTA',
            ],
            [
                'name'     => 'Carlos',
                'surname'  => 'Ramírez',
                'email'    => 'enfermero@clinica.com',
                'password' => bcrypt('12345678'),
                'role'     => 'ENFERMERO',
            ],
            [
                'name'     => 'Dr. José',
                'surname'  => 'Pérez',
                'email'    => 'doctor@clinica.com',
                'password' => bcrypt('12345678'),
                'role'     => 'DOCTOR',
            ],
        ];

        foreach ($usuarios as $data) {
            $roleName = $data['role'];
            unset($data['role']);

            $user = \App\Models\User::firstOrCreate(['email' => $data['email']], $data);

            if ($user->roles->isEmpty()) {
                $role = Role::where('name', $roleName)->where('guard_name', 'api')->first();
                if ($role) $user->assignRole($role);
            }
        }

        $this->command->info('Permisos, roles y usuarios creados correctamente:');
        $this->command->table(
            ['Rol', 'Email', 'Contraseña'],
            [
                ['Super-Admin',   'dquinteros630@gmail.com',    '12345678'],
                ['ADMINISTRADOR', 'admin@clinica.com',          '12345678'],
                ['RECEPCIONISTA', 'recepcionista@clinica.com',  '12345678'],
                ['ENFERMERO',     'enfermero@clinica.com',      '12345678'],
                ['DOCTOR',        'doctor@clinica.com',         '12345678'],
            ]
        );
    }
}

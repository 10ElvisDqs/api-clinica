<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Asegurar que todos los permisos existen ──────────────────────
        $todos = [
            // Roles
            'register_rol','list_rol','edit_rol','delete_rol',
            // Doctores
            'register_doctor','list_doctor','edit_doctor','delete_doctor','profile_doctor',
            // Pacientes
            'register_patient','list_patient','edit_patient','delete_patient','profile_patient',
            // Staff
            'register_staff','list_staff','edit_staff','delete_staff',
            // Citas
            'register_appointment','list_appointment','edit_appointment','delete_appointment',
            // Especialidades
            'register_specialty','list_specialty','edit_specialty','delete_specialty',
            // Pagos
            'show_payment','edit_payment',
            // Actividad y calendario
            'activitie','calendar',
            // Reportes
            'expense_report','invoice_report',
            // Configuración
            'settings',
            // Ingreso / Egreso / Seguimiento
            'list_ingreso','register_ingreso','edit_ingreso','delete_ingreso',
            'list_egreso','register_egreso','edit_egreso','delete_egreso',
            'list_seguimiento','register_seguimiento','edit_seguimiento','delete_seguimiento',
        ];

        foreach ($todos as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']);
        }

        $todos[] = 'view_own_profile';
        $todos[] = 'view_own_appointments';
        $todos[] = 'view_own_history';
        $todos[] = 'view_own_seguimientos';
        foreach (['view_own_profile','view_own_appointments','view_own_history','view_own_seguimientos'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'api']);
        }

        // ── 2. Crear roles con sus permisos ─────────────────────────────────

        // ADMINISTRADOR — acceso total (igual que Super-Admin pero visible en staffs)
        $admin = Role::firstOrCreate(['name' => 'ADMINISTRADOR', 'guard_name' => 'api']);
        $admin->syncPermissions($todos);

        // RECEPCIONISTA — pacientes, citas, pagos, historial (ver)
        $recep = Role::firstOrCreate(['name' => 'RECEPCIONISTA', 'guard_name' => 'api']);
        $recep->syncPermissions([
            'list_patient','register_patient','edit_patient','profile_patient',
            'list_appointment','register_appointment','edit_appointment',
            'show_payment','edit_payment',
            'activitie',
            'list_seguimiento',
        ]);

        // ENFERMERO — pacientes, calendario, historial (ver), ingreso, egreso
        $enfermero = Role::firstOrCreate(['name' => 'ENFERMERO', 'guard_name' => 'api']);
        $enfermero->syncPermissions([
            'list_patient','register_patient','edit_patient','profile_patient',
            'calendar','activitie',
            'list_ingreso','register_ingreso','edit_ingreso',
            'list_egreso','register_egreso','edit_egreso',
            'list_seguimiento',
        ]);

        // PACIENTE — solo ve su propia información
        $paciente = Role::firstOrCreate(['name' => 'PACIENTE', 'guard_name' => 'api']);
        $paciente->syncPermissions([
            'view_own_profile', 'view_own_appointments', 'view_own_history', 'view_own_seguimientos',
        ]);

        // DOCTOR — pacientes (info), calendario, diagnóstico, reporte
        $doctor = Role::firstOrCreate(['name' => 'DOCTOR', 'guard_name' => 'api']);
        $doctor->syncPermissions([
            'list_patient','profile_patient',
            'calendar','activitie',
            'list_seguimiento','register_seguimiento','edit_seguimiento',
            'invoice_report',
        ]);

        // ── 3. Crear usuarios de prueba ─────────────────────────────────────

        $usuarios = [
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

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Asignar rol solo si aún no tiene ninguno
            if ($user->roles->isEmpty()) {
                $role = Role::where('name', $roleName)->where('guard_name', 'api')->first();
                if ($role) $user->assignRole($role);
            }
        }

        $this->command->info('Roles y usuarios creados correctamente:');
        $this->command->table(
            ['Rol', 'Email', 'Contraseña'],
            [
                ['ADMINISTRADOR', 'admin@clinica.com',          '12345678'],
                ['RECEPCIONISTA', 'recepcionista@clinica.com',  '12345678'],
                ['ENFERMERO',     'enfermero@clinica.com',      '12345678'],
                ['DOCTOR',        'doctor@clinica.com',         '12345678'],
                ['PACIENTE',      '(se crea al registrarse)',   '(la que elija)'],
            ]
        );
    }
}

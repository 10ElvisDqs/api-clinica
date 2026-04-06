<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Doctor\DoctorScheduleDay;
use App\Models\Doctor\DoctorScheduleJoinHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $roleDoctor = Role::where('name', 'DOCTOR')->where('guard_name', 'api')->first();

        $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

        // IDs de slots de horario (08:00 - 14:00 = primeros 24 slots)
        $horasMañana = range(1, 24);

        $doctores = [
            ['name' => 'Dr. Carlos',   'surname' => 'Mendoza',    'email' => 'dr.mendoza@clinica.com'],
            ['name' => 'Dra. Ana',     'surname' => 'Rodríguez',  'email' => 'dr.rodriguez@clinica.com'],
            ['name' => 'Dr. Luis',     'surname' => 'Fernández',  'email' => 'dr.fernandez@clinica.com'],
            ['name' => 'Dra. María',   'surname' => 'Quispe',     'email' => 'dr.quispe@clinica.com'],
            ['name' => 'Dr. Jorge',    'surname' => 'Vargas',     'email' => 'dr.vargas@clinica.com'],
        ];

        foreach ($doctores as $data) {
            // Crear usuario doctor
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'surname'  => $data['surname'],
                    'password' => bcrypt('12345678'),
                ]
            );

            if ($user->roles->isEmpty() && $roleDoctor) {
                $user->assignRole($roleDoctor);
            }

            // Asignar días y horarios
            foreach ($dias as $dia) {
                $scheduleDay = DoctorScheduleDay::firstOrCreate([
                    'user_id' => $user->id,
                    'day'     => $dia,
                ]);

                // Asignar los primeros 24 slots (08:00 - 14:00) si no tiene ninguno
                if ($scheduleDay->wasRecentlyCreated) {
                    foreach ($horasMañana as $hourId) {
                        DoctorScheduleJoinHour::create([
                            'doctor_schedule_day_id'  => $scheduleDay->id,
                            'doctor_schedule_hour_id' => $hourId,
                        ]);
                    }
                }
            }
        }

        $this->command->info('5 doctores creados con horarios Lun-Vie 08:00-14:00:');
        $this->command->table(
            ['Nombre', 'Email', 'Contraseña'],
            array_map(fn($d) => [$d['name'] . ' ' . $d['surname'], $d['email'], '12345678'], $doctores)
        );
    }
}

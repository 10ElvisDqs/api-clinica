<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionsDemoSeeder::class,  // 1. Roles, permisos y usuarios de staff
            SpecialitySeeder::class,        // 2. Especialidades médicas
            ScheduleHourSeeder::class,      // 3. 40 slots de horario (08:00 - 18:00)
            DoctorSeeder::class,            // 4. 5 doctores con días y horarios
            PatientSeeder::class,           // 5. 100 pacientes con usuario vinculado
            AppointmentSeeder::class,       // 6. 1000 citas con pagos y atenciones
        ]);
    }
}

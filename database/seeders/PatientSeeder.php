<?php

namespace Database\Seeders;

use App\Models\Patient\Patient;
use App\Models\Patient\PatientPerson;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $rolePaciente = Role::where('name', 'PACIENTE')->where('guard_name', 'api')->first();
        $relaciones   = ['Mamá', 'Papá', 'Hermano', 'Tío', 'Esposo/a'];

        Patient::factory()->count(100)->create()->each(function ($patient) use ($rolePaciente, $relaciones) {

            // Crear acompañante/responsable
            PatientPerson::create([
                'patient_id'               => $patient->id,
                'name_companion'           => fake()->firstName(),
                'surname_companion'        => fake()->lastName(),
                'mobile_companion'         => fake()->numerify('9########'),
                'relationship_companion'   => $relaciones[array_rand($relaciones)],
                'name_responsible'         => fake()->firstName(),
                'surname_responsible'      => fake()->lastName(),
                'mobile_responsible'       => fake()->numerify('9########'),
                'relationship_responsible' => $relaciones[array_rand($relaciones)],
            ]);

            // Crear usuario vinculado para que pueda iniciar sesión
            $email = 'paciente' . $patient->id . '@clinica.com';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => $patient->name,
                    'surname'  => $patient->surname,
                    'password' => bcrypt('12345678'),
                ]
            );

            if ($user->roles->isEmpty() && $rolePaciente) {
                $user->assignRole($rolePaciente);
            }

            // Vincular usuario al paciente
            $patient->update(['user_id' => $user->id, 'email' => $email]);
        });

        $this->command->info('100 pacientes cargados con nombres aleatorios.');
        $this->command->info('Patrón de login: paciente{id}@clinica.com / 12345678');
        $this->command->info('Ejemplo: paciente1@clinica.com, paciente2@clinica.com ...');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecialitySeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = [
            ['name' => 'Anestesiología',              'price' => 150.00],
            ['name' => 'Anatomía Patológica',          'price' => 200.00],
            ['name' => 'Cardiología',                  'price' => 250.00],
            ['name' => 'Cirugía Pediátrica',           'price' => 300.00],
            ['name' => 'Cirugía General',              'price' => 280.00],
            ['name' => 'Dermatología',                 'price' => 120.00],
            ['name' => 'Gastroenterología',            'price' => 180.00],
            ['name' => 'Ginecología y Obstetricia',    'price' => 160.00],
            ['name' => 'Neurología',                   'price' => 220.00],
            ['name' => 'Odontología',                  'price' => 90.00],
            ['name' => 'Oftalmología',                 'price' => 130.00],
            ['name' => 'Pediatría',                    'price' => 100.00],
            ['name' => 'Traumatología',                'price' => 200.00],
            ['name' => 'Medicina General',             'price' => 80.00],
        ];

        foreach ($especialidades as $esp) {
            DB::table('specialities')->updateOrInsert(
                ['name' => $esp['name']],
                ['price' => $esp['price'], 'state' => 1, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $this->command->info(count($especialidades) . ' especialidades cargadas con precio.');
    }
}

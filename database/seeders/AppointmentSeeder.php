<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentPay;
use App\Models\Appointment\AppointmentAttention;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Appointment::factory()->count(1000)->create()->each(function($p) {
            $faker = \Faker\Factory::create();
            if($p->status == 2){
                AppointmentAttention::create([ 
                    "appointment_id" => $p->id,
                    "patient_id" => $p->patient_id,
                    "description" => $faker->text($maxNbChars = 300),
                    "receta_medica" =>  json_encode([
                        [
                            "name_medical" => $faker->word(),
                            "uso" => $faker->word(),
                        ],
                    ])
                ]);
            }
            if($p->status_pay == 2){
                AppointmentPay::create([
                    "appointment_id" => $p->id,
                    "amount" => 50,
                    "method_payment" => $faker->randomElement(["EFECTIVO","TRANSFERENCIA","YAPE","PLIN"]),
                ]);
            }else{
                AppointmentPay::create([
                    "appointment_id" => $p->id,
                    "amount" => $p->amount,
                    "method_payment" => $faker->randomElement(["EFECTIVO","TRANSFERENCIA","YAPE","PLIN"]),
                ]);
            }
        });
        // php artisan db:seed --class=AppointmentSeeder
    }
}

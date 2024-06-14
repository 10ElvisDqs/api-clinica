<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Appointment\Appointment;

class NotificationAppointmentWasap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification-appointment-wasap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notificar al paciente 1 hora antes de su cita medica , por medio de whatsap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        date_default_timezone_set("America/Lima");
        $simulet_hour_number = date("2023-10-24 09:35:35");//strtotime(date("2023-10-24 09:35:35"));
        $appointments = Appointment::whereDate("date_appointment","2023-10-24")//now()->format("Y-m-d")
                                    ->where("status",1)
                                    ->get();
        $now_time_number = strtotime($simulet_hour_number);//now()->format("Y-m-d h:i:s")
        $patients = collect([]);
        foreach ($appointments as $key => $appointment) {
            $hour_start = $appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_start;
            $hour_end = $appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_end;

            // 2023-10-25 08:30:00 -> 2023-10-25 07:30:00
            $hour_start = strtotime(Carbon::parse(date("Y-m-d")." ".$hour_start)->subHour());
            $hour_end = strtotime(Carbon::parse(date("Y-m-d")." ".$hour_end)->subHour());

            if($hour_start <= $now_time_number && $hour_end >= $now_time_number){
                $patients->push([
                    "name" => $appointment->patient->name,
                    "surname" => $appointment->patient->surname,
                    "avatar" => $appointment->avatar ? env("APP_URL")."storage/".$appointment->avatar : NULL,
                    "email" => $appointment->patient->email,
                    "mobile" => $appointment->patient->mobile,
                    "doctor_full_name" => $appointment->doctor->name.' '.$appointment->doctor->surname,
                    "specialitie_name" => $appointment->specialitie->name,
                    "n_document" => $appointment->patient->n_document,
                    "hour_start_format" => Carbon::parse(date("Y-m-d")." ".$appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_start)->format("h:i A"),
                    "hour_end_format" => Carbon::parse(date("Y-m-d")." ".$appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_end)->format("h:i A"),
                ]);
            }
        }

        foreach ($patients as $key => $patient) {
            $accessToken = 'EAAFQqJKpYMkBOxNIdWvoqssP99X8EXTiwXNZAZCj3o5mGrRAqBRrogJExb5KW6izK8iNQWL1fhZCzOeve9GFv0wN0PTaRntfk2ihLHvlBkGSSZAngBvqXJEEYatRSFikbOSCurEz9EH5ZBoROFE4ZCXtYoBpO2mUCHSl0YZCZASeV0F4hJ9fNJuR6WO3mCBsotfbix6lif9oY6P5PPbeSrLB2Dz49DiRm87i';

            $fbApiUrl = 'https://graph.facebook.com/v17.0/XXXXXXXXXXXXXXXXX/messages';

            $data = [
                'messaging_product' => 'whatsapp',
                'to' => 'xxxxxxxxxxxxxxx',
                'type' => 'template',
                'template' => [
                    'name' => 'recordatorio',
                    'language' => [
                        'code' => 'es_MX',
                    ],
                    "components"=>  [
                        [
                            "type" =>  "header",
                            "parameters"=>  [
                                [
                                    "type"=>  "text",
                                    "text"=>  $patient["name"].' '.$patient["surname"],
                                ]
                            ]
                        ],
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type"=> "text",
                                    "text"=>  $patient["hour_start_format"].' '. $patient["hour_end_format"],
                                ],
                                [
                                    "type"=> "text",
                                    "text"=>  $patient["doctor_full_name"]
                                ],
                            ]
                        ],
                    ],
                ],
            ];

            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ];

            $ch = curl_init($fbApiUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            echo "HTTP Code: $httpCode\n";
            echo "Response:\n$response\n";
        }

        dd($patients);
    }
}

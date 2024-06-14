<?php

namespace App\Http\Controllers\Admin\Doctor;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Doctor\Specialitie;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use Illuminate\Support\Facades\Storage;
use App\Models\Doctor\DoctorScheduleDay;
use App\Http\Resources\User\UserResource;
use App\Models\Doctor\DoctorScheduleHour;
use App\Http\Resources\User\UserCollection;
use App\Models\Doctor\DoctorScheduleJoinHour;
use App\Http\Resources\Appointment\AppointmentCollection;
use Illuminate\Support\Facades\Redis;

class DoctorsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $users = User::where(DB::raw("CONCAT(users.name,' ',IFNULL(users.surname,''),' ',users.email)"),"like","%".$search."%")
                        // "name","like","%".$search."%"
                        // ->orWhere("surname","like","%".$search."%")
                        // ->orWhere("email","like","%".$search."%")
                        ->orderBy("id","desc")
                        ->whereHas("roles",function($q){
                            $q->where("name","like","%DOCTOR%");
                        })
                        ->get();

        return response()->json([
            "users" => UserCollection::make($users),
        ]);
    }

    public function profile($id){
        //$user = User::findOrFail($id);
//
        //$num_appointment = Appointment::where("doctor_id",$id)->count();
        //$money_of_appointments = Appointment::where("doctor_id",$id)->sum("amount");
        //$num_appointment_pendings = Appointment::where("doctor_id",$id)->where("status",1)->count();
//
        //$appointment_pendings = Appointment::where("doctor_id",$id)->where("status",1)->get();
        //$appointments = Appointment::where("doctor_id",$id)->get();
        //return response()->json([
        //    "num_appointment" => $num_appointment,
        //    "money_of_appointments" => $money_of_appointments,
        //    "num_appointment_pendings" => $num_appointment_pendings,
        //    "doctor" => UserResource::make($user),
        //    "appointment_pendings" => AppointmentCollection::make($appointment_pendings),
        //    "appointments" => $appointments->map(function($appointment){
        //        return [
        //            "id" => $appointment->id,
        //            "patient" => [
        //                "id" => $appointment->patient->id,
        //                "full_name" => $appointment->patient->name . ' ' .$appointment->patient->surname,
        //                "avatar" => $appointment->patient->avatar ? env("APP_URL")."storage/".$appointment->patient->avatar : 'https://cdn-icons-png.flaticon.com/512/1430/1430453.png',
        //            ],
        //            "doctor" => [
        //                "id" => $appointment->doctor->id,
        //                "full_name" => $appointment->doctor->name . ' ' .$appointment->doctor->surname,
        //                "avatar" => $appointment->doctor->avatar ? env("APP_URL")."storage/".$appointment->doctor->avatar : NULL,
        //            ],
        //            "date_appointment" => $appointment->date_appointment,
        //            "date_appointment_format" => Carbon::parse($appointment->date_appointment)->format("d M Y"),
        //            "format_hour_start" => Carbon::parse(date("Y-m-d").' '.$appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_start)->format("h:i A"),
        //            "format_hour_end" => Carbon::parse(date("Y-m-d").' '.$appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_end)->format("h:i A"),
        //            "appointment_attention" => $appointment->attention ? [
        //                "id" => $appointment->attention->id,
        //                "description" => $appointment->attention->description,
        //                "receta_medica" => $appointment->attention->receta_medica ? json_decode($appointment->attention->receta_medica) : [],
        //                "created_at" => $appointment->attention->created_at->format("Y-m-d h:i A"),
        //            ] : NULL,
        //            "amount" => $appointment->amount,
        //            "status_pay" => $appointment->status_pay,
        //            "status" => $appointment->status,
        //        ];
        //    }),
        //]);
        $cachedRecord = Redis::get('profile_doctor_#'.$id);
        $data_doctor = [];
        if(isset($cachedRecord)) {
            $data_doctor = json_decode($cachedRecord, FALSE);
        }else{
            $user = User::findOrFail($id);
            $num_appointment = Appointment::where("doctor_id",$id)->count();
            $money_of_appointments = Appointment::where("doctor_id",$id)->sum("amount");
            $num_appointment_pendings = Appointment::where("doctor_id",$id)->where("status",1)->count();

            $appointment_pendings = Appointment::where("doctor_id",$id)->where("status",1)->get();
            $appointments = Appointment::where("doctor_id",$id)->get();

            $data_doctor = [
                "num_appointment" => $num_appointment,
                "money_of_appointments" => $money_of_appointments,
                "num_appointment_pendings" => $num_appointment_pendings,
                "doctor" => UserResource::make($user),
                "appointment_pendings" => AppointmentCollection::make($appointment_pendings),
                "appointments" => $appointments->map(function($appointment){
                    return [
                        "id" => $appointment->id,
                        "patient" => [
                            "id" => $appointment->patient->id,
                            "full_name" => $appointment->patient->name . ' ' .$appointment->patient->surname,
                            "avatar" => $appointment->patient->avatar ? env("APP_URL")."storage/".$appointment->patient->avatar : 'https://cdn-icons-png.flaticon.com/512/1430/1430453.png',
                        ],
                        "doctor" => [
                            "id" => $appointment->doctor->id,
                            "full_name" => $appointment->doctor->name . ' ' .$appointment->doctor->surname,
                            "avatar" => $appointment->doctor->avatar ? env("APP_URL")."storage/".$appointment->doctor->avatar : NULL,
                        ],
                        "date_appointment" => $appointment->date_appointment,
                        "date_appointment_format" => Carbon::parse($appointment->date_appointment)->format("d M Y"),
                        "format_hour_start" => Carbon::parse(date("Y-m-d").' '.$appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_start)->format("h:i A"),
                        "format_hour_end" => Carbon::parse(date("Y-m-d").' '.$appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_end)->format("h:i A"),
                        "appointment_attention" => $appointment->attention ? [
                            "id" => $appointment->attention->id,
                            "description" => $appointment->attention->description,
                            "receta_medica" => $appointment->attention->receta_medica ? json_decode($appointment->attention->receta_medica) : [],
                            "created_at" => $appointment->attention->created_at->format("Y-m-d h:i A"),
                        ] : NULL,
                        "amount" => $appointment->amount,
                        "status_pay" => $appointment->status_pay,
                        "status" => $appointment->status,
                    ];
                }),
            ];
            Redis::set('profile_doctor_#'.$id, json_encode($data_doctor),'EX', 3600);
        }

        return response()->json($data_doctor);
    }

    public function config() {
        $roles = Role::where("name","like","%DOCTOR%")->get();

        $specialities = Specialitie::where("state",1)->get();

        $hours_days = collect([]);

        $doctor_schedule_hours = DoctorScheduleHour::all();
        foreach ($doctor_schedule_hours->groupBy("hour") as $key => $schedule_hour) {
            $hours_days->push([
                "hour" => $key,
                "format_hour" => Carbon::parse(date("Y-m-d").' '.$key.":00:00")->format("h:i A"),
                "items" => $schedule_hour->map(function($hour_item) {
                    // Y-m-d h:i:s 2023-10-2 00:13:30 -> 12:13:20
                    return [
                        "id" => $hour_item->id,
                        "hour_start" => $hour_item->hour_start,
                        "hour_end" => $hour_item->hour_end,
                        "format_hour_start" => Carbon::parse(date("Y-m-d").' '.$hour_item->hour_start)->format("h:i A"),
                        "format_hour_end" => Carbon::parse(date("Y-m-d").' '.$hour_item->hour_end)->format("h:i A"),
                        "hour" => $hour_item->hour,
                    ];
                }),
            ]);
        }
        return response()->json([
            "roles" => $roles,
            "specialities" => $specialities,
            "hours_days" => $hours_days,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $schedule_hours = json_decode($request->schedule_hours,1);
        //dd($schedule_hours);

        $users_is_valid = User::where("email",$request->email)->first();

        if($users_is_valid){
            return response()->json([
                "message" => 403,
                "message_text" => "EL USUARIO CON ESTE EMAIL YA EXISTE"
            ]);
        }

        if($request->hasFile("imagen")){
            $path = Storage::putFile("staffs",$request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }

        if($request->password){
            $request->request->add(["password" => bcrypt($request->password)]);
        }
        // "Fri Oct 08 1993 00:00:00 GMT-0500 (hora estándar de Perú)"
        // Eliminar la parte de la zona horaria (GMT-0500 y entre paréntesis)
        $date_clean = preg_replace('/\(.*\)|[A-Z]{3}-\d{4}/', '', $request->birth_date);

        $request->request->add(["birth_date" => Carbon::parse($date_clean)->format("Y-m-d h:i:s")]);

        $user = User::create($request->all());

        $role = Role::findOrFail($request->role_id);
        $user->assignRole($role);

        // ALMACENAR LA DISPONIBILIDAD DE HORARIO DEL DOCTOR

        foreach ($schedule_hours as $key => $schedule_hour) {
            if(sizeof($schedule_hour["children"]) > 0){
                $schedule_day = DoctorScheduleDay::create([
                    "user_id" => $user->id,
                    "day" => $schedule_hour["day_name"],
                ]);

                foreach ($schedule_hour["children"] as $children) {
                    DoctorScheduleJoinHour::create([
                        "doctor_schedule_day_id" => $schedule_day->id,
                        "doctor_schedule_hour_id" => $children["item"]["id"],
                    ]);
                }
            }
        }
        return response()->json([
            "message" => 200,
            "data"=>$request->all()
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            "doctor" => UserResource::make($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schedule_hours = json_decode($request->schedule_hours,1);

        $users_is_valid = User::where("id","<>",$id)->where("email",$request->email)->first();

        if($users_is_valid){
            return response()->json([
                "message" => 403,
                "message_text" => "EL USUARIO CON ESTE EMAIL YA EXISTE"
            ]);
        }

        $user = User::findOrFail($id);

        if($request->hasFile("imagen")){
            if($user->avatar){
                Storage::delete($user->avatar);
            }
            $path = Storage::putFile("staffs",$request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }

        if($request->password){
            $request->request->add(["password" => bcrypt($request->password)]);
        }

        $date_clean = preg_replace('/\(.*\)|[A-Z]{3}-\d{4}/', '', $request->birth_date);

        $request->request->add(["birth_date" => Carbon::parse($date_clean)->format("Y-m-d h:i:s")]);

        // $request->request->add(["birth_date" => Carbon::parse($request->birth_date, 'GMT')->format("Y-m-d h:i:s")]);
        $cachedRecord = Redis::get('profile_doctor_#'.$id);
        if(isset($cachedRecord)) {
            Redis::del('profile_doctor_#'.$id);
        }
        $user->update($request->all());

        if($request->role_id != $user->roles()->first()->id){
            $role_old = Role::findOrFail($user->roles()->first()->id);
            $user->removeRole($role_old);

            $role_new = Role::findOrFail($request->role_id);
            $user->assignRole($role_new);
        }

        // ALMACENAR LA DISPONIBILIDAD DE HORARIO DEL DOCTOR
        // foreach ($user->schedule_days as $key => $schedule_day) {
        //     $schedule_day->delete();
        // }

        // foreach ($schedule_hours as $key => $schedule_hour) {
        //     if(sizeof($schedule_hour["children"]) > 0){
        //         $schedule_day = DoctorScheduleDay::create([
        //             "user_id" => $user->id,
        //             "day" => $schedule_hour["day_name"],
        //         ]);

        //         foreach ($schedule_hour["children"] as $children) {
        //             DoctorScheduleJoinHour::create([
        //                 "doctor_schedule_day_id" => $schedule_day->id,
        //                 "doctor_schedule_hour_id" => $children["item"]["id"],
        //             ]);
        //         }
        //     }
        // }




        // VAMOS A COMPROBAR SI TODO SIGUE IGUAL O SI SE HA BORRADO ALGUN DIA
        foreach ($user->schedule_days as $key => $schedule_day) {
            // DEFINIMOS UNA BANDERA PARA PODER SABER SI BORRADO UN DIA : TRUE - EXISTE / FALSE - ELIMINADO
            $is_exists_schedule_day = false;
            // DE LO LLENADOS EN EL HORARIO DEL DOCTOR ITERAMOS PARA HACER LA COMPROBACIÓN
            foreach ($schedule_hours as $key => $schedule_hour) {
                // COMPROBAMOS QUE HAY SEGMENTOS SELECCIONADOS
                if(sizeof($schedule_hour["children"]) > 0){
                    if($schedule_day->day == $schedule_hour["day_name"]){
                        // SI HAY UNA COINCIDENCIA ENTONCES EL DIA QUE TENEMOS REGISTRADO ES EL MISMO QUE ESTAMOS
                        // ENVIANDO EN EL FRONTED , ESTO NOS SIRVE PARA NO TENER QUE ELIMINARLO SINO QUE SIGA
                        // SU FUNCIONAMIENTO
                        $is_exists_schedule_day = true;
                    }
                    if($is_exists_schedule_day){
                        // AHORA TENEMOS QUE COMPROBAR DE ESE DIA SI SUS SEGMENTOS ESTAN CORRECTOS Y NO
                        // HAN ELIMINADO NINGUNO
                        foreach ($schedule_day->schedules_hours as $schedules_hour) {
                            // DEFINIMOS UNA BANDERA PARA PODER SABER SI BORRADO UN SEGMENTO : TRUE - EXISTE / FALSE - ELIMINADO
                            $is_exists_schedules_hour = false;
                            // SEGMENTOS SELECCIONADOS
                            foreach ($schedule_hour["children"] as $children) {
                                if($schedules_hour->doctor_schedule_hour_id == $children["item"]["id"]){
                                    $is_exists_schedules_hour = true;
                                    break;
                                }
                            }
                            if(!$is_exists_schedules_hour){
                                $schedules_hour->delete();
                            }
                        }
                        break;
                    }
                }
            }
            if(!$is_exists_schedule_day){
                // AL NO EXISTIR EL DIA TENEMOS QUE ELIMINAR TANTO LOS SEGMENTOS COMO EL DIA EN SI
                foreach ($schedule_day->schedules_hours as $schedules_hour) {
                    $schedules_hour->delete();
                }
                $schedule_day->delete();
            }
        }
        // VAMOS A COMPROBAR SI TODO ESTA IGUAL A LO QUE MANDAMOS O SI SE HA AGREGADO ALGUN DIA
        foreach ($schedule_hours as $key => $schedule_hour) {
            // COMPROBAMOS QUE HAY SEGMENTOS SELECCIONADOS
            if(sizeof($schedule_hour["children"]) > 0){
                $is_exists_schedule_day = false;
                // DE LOS REGISTROS QUE TENEMOS DISPONIBLES EN LA BD
                foreach ($user->schedule_days as $key => $schedule_day) {
                    if($schedule_day->day == $schedule_hour["day_name"]){
                         // SI HAY UNA COINCIDENCIA ENTONCES EL DIA QUE TENEMOS REGISTRADO ES EL MISMO QUE ESTAMOS
                        // ENVIANDO EN EL FRONTED , ESTO NOS SIRVE PARA NO TENER QUE AGREGAR SINO QUE SIGA
                        // SU FUNCIONAMIENTO
                        $is_exists_schedule_day = true;
                        // break;
                    }
                    if($is_exists_schedule_day){
                        // AHORA TENEMOS QUE COMPROBAR DE ESE DIA SI SUS SEGMENTOS ESTAN CORRECTOS Y NO
                        // HAN AGREGADO NINGUNO
                        foreach ($schedule_hour["children"] as $children) {
                            $is_exists_schedules_hour = false;
                            foreach ($schedule_day->schedules_hours as $schedules_hour) {
                                if($schedules_hour->doctor_schedule_hour_id == $children["item"]["id"]){
                                    $is_exists_schedules_hour = true;
                                    break;
                                }
                            }
                            if(!$is_exists_schedules_hour){
                                DoctorScheduleJoinHour::create([
                                    "doctor_schedule_day_id" => $schedule_day->id,
                                    "doctor_schedule_hour_id" => $children["item"]["id"],
                                ]);
                            }
                        }
                        break;
                    }
                }
                if(!$is_exists_schedule_day){
                    // AL NO EXISTIR EL DIA TENEMOS QUE AGREGAR TANTO LOS SEGMENTOS COMO EL DIA EN SI
                    $schedule_day = DoctorScheduleDay::create([
                        "user_id" => $user->id,
                        "day" => $schedule_hour["day_name"],
                    ]);

                    foreach ($schedule_hour["children"] as $children) {
                        DoctorScheduleJoinHour::create([
                            "doctor_schedule_day_id" => $schedule_day->id,
                            "doctor_schedule_hour_id" => $children["item"]["id"],
                        ]);
                    }
                }
            }
        }

        return response()->json([
            "message" => 200
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        $cachedRecord = Redis::get('profile_doctor_#'.$id);
        if(isset($cachedRecord)) {
            Redis::del('profile_doctor_#'.$id);
        }
        return response()->json([
            "message" => 200
        ]);
    }
}

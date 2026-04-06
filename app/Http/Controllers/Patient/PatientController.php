<?php

namespace App\Http\Controllers\Patient;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Patient\Patient;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Patient\PatientPerson;
use App\Models\Appointment\Appointment;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Patient\PatientResource;
use App\Http\Resources\Patient\PatientCollection;
use App\Http\Resources\Appointment\AppointmentCollection;
use Illuminate\Support\Facades\Redis;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny',Patient::class);
        $search = $request->search;

        $patients = Patient::where(DB::raw("CONCAT(patients.name,' ',IFNULL(patients.surname,''),' ',patients.n_document)"),"like","%".$search."%")
                        ->orderBy("id","desc")
                        ->paginate(20);

        return response()->json([
            "total" => $patients->total(),
            "patients" => PatientCollection::make($patients),
        ]);
    }

    public function profile($id) {
        $this->authorize('profile',Patient::class);
        $cachedRecord = Redis::get('profile_patient_#'.$id);
        $data_patient = [];
        if(isset($cachedRecord)) {
            $data_patient = json_decode($cachedRecord, FALSE);
        }else{

            $patient = Patient::findOrFail($id);

            $num_appointment = Appointment::where("patient_id",$id)->count();
            $money_of_appointments = Appointment::where("patient_id",$id)->sum("amount");
            $num_appointment_pendings = Appointment::where("patient_id",$id)->where("status",1)->count();

            $appointment_pendings = Appointment::where("patient_id",$id)->where("status",1)->get();
            $appointments = Appointment::where("patient_id",$id)->get();
            $data_patient = [
                "num_appointment" => $num_appointment,
                "money_of_appointments" => $money_of_appointments,
                "num_appointment_pendings" => $num_appointment_pendings,
                "patient" => PatientResource::make($patient),
                "appointment_pendings" => AppointmentCollection::make($appointment_pendings),
                "appointments" => $appointments->map(function($appointment){
                    return [
                        "id" => $appointment->id,
                        "patient" => [
                            "id" => $appointment->patient->id,
                            "full_name" => $appointment->patient->name . ' ' .$appointment->patient->surname,
                            "avatar" => $appointment->patient->avatar ? env("APP_URL")."storage/".$appointment->patient->avatar : NULL,
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
            Redis::set('profile_patient_#'.$id, json_encode($data_patient),'EX', 3600);
        }


        return response()->json($data_patient);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create',Patient::class);
        $patient_is_valid = Patient::where("n_document",$request->n_document)->first();

        if($patient_is_valid){
            return response()->json([
                "message" => 403,
                "message_text" => "EL PACIENTE YA EXISTE"
            ]);
        }

        if($request->hasFile("imagen")){
            $path = Storage::putFile("patients",$request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }

        // "Fri Oct 08 1993 00:00:00 GMT-0500 (hora estándar de Perú)"
        // Eliminar la parte de la zona horaria (GMT-0500 y entre paréntesis)
        if($request->birth_date){
            $date_clean = preg_replace('/\(.*\)|[A-Z]{3}-\d{4}/', '', $request->birth_date);

            $request->request->add(["birth_date" => Carbon::parse($date_clean)->format("Y-m-d h:i:s")]);
        }

        $patient = Patient::create($request->all());

        // Crear la relación en la tabla 'patient_persons' solo si se proporciona información válida
        if ($request->has('name_companion')) {
            $data = $request->only([
                'name_companion',
                'surname_companion',
                'mobile_companion',
                'relationship_companion',
                'name_responsible',
                'surname_responsible',
                'mobile_responsible',
                'relationship_responsible',
            ]);
            $data['patient_id'] = $patient->id; // Añadir el ID del paciente

            PatientPerson::create($data);
        }

        return response()->json([
            "message" => 200,
            "msg"=> "SE CREO EXITOSAMENTE",
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('view',Patient::class);
        $patient = Patient::findOrFail($id);

        return response()->json([
            "patient" => PatientResource::make($patient),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('update',Patient::class);
        $patient_is_valid = Patient::where("id","<>",$id)->where("n_document",$request->n_document)->first();

        if($patient_is_valid){
            return response()->json([
                "message" => 403,
                "message_text" => "EL PACIENTE YA EXISTE"
            ]);
        }

        $patient = Patient::findOrFail($id);

        if($request->hasFile("imagen")){
            if($patient->avatar){
                Storage::delete($patient->avatar);
            }
            $path = Storage::putFile("patients",$request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }

        if($request->birth_date){
            $date_clean = preg_replace('/\(.*\)|[A-Z]{3}-\d{4}/', '', $request->birth_date);

            $request->request->add(["birth_date" => Carbon::parse($date_clean)->format("Y-m-d h:i:s")]);
        }

        $cachedRecord = Redis::get('profile_patient_#'.$id);
        if(isset($cachedRecord)) {
            Redis::del('profile_patient_#'.$id);
        }
        // $request->request->add(["birth_date" => Carbon::parse($request->birth_date, 'GMT')->format("Y-m-d h:i:s")]);
        $patient->update($request->all());

        // Crear la relación en la tabla 'patient_persons' solo si se proporciona información válida
        if ($request->has('name_companion')) {
            $patientPerson = PatientPerson::where('patient_id', $patient->id)->first();



            if ($patientPerson) {
                $patientPerson->update($request->only([
                    'name_companion',
                    'surname_companion',
                    'mobile_companion',
                    'relationship_companion',
                    'name_responsible',
                    'surname_responsible',
                    'mobile_responsible',
                    'relationship_responsible',
                ]));
            }else{
                // Si el paciente no existe, crea uno nuevo
                $data = $request->only([
                    'name_companion',
                    'surname_companion',
                    'mobile_companion',
                    'relationship_companion',
                    'name_responsible',
                    'surname_responsible',
                    'mobile_responsible',
                    'relationship_responsible',
                ]);
                $data['patient_id'] = $patient->id; // Añadir el ID del paciente

                PatientPerson::create($data);
            }
        }

        return response()->json([
            "message" => 200,
            "msg"=>"SE ACTUALIZO EL PACIENTE EXITOSAMENTE"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete',Patient::class);
        $patient = Patient::findOrFail($id);
        if($patient->avatar){
            Storage::delete($patient->avatar);
        }

        $cachedRecord = Redis::get('profile_patient_#'.$id);
        if(isset($cachedRecord)) {
            Redis::del('profile_patient_#'.$id);
        }

        $patient->delete();
        return response()->json([
            "message" => 200,
            "msg"=>"SE ELIMINO CORRECTAMENTE"
        ]);
    }

    public function reporte(Request $request)
    {
        $this->authorize('viewAny',Patient::class);
        $search = $request->search;

        $patients = Patient::where(DB::raw("CONCAT(patients.name,' ',IFNULL(patients.surname,''),' ',patients.n_document)"),"like","%".$search."%")
                        ->orderBy("id","desc")
                        ->paginate(20);

        $resultado = PatientCollection::make($patients);
        $resultado = $resultado->toJson();
        $resultado = json_decode($resultado, true);
        // Generar el PDF
        // dd($resultado);
        $pdf = PDF::loadView('ReportePatient.pdf', compact('resultado'));
        // Retornar el PDF como respuesta
        return $pdf->download('reporte_patients.pdf');
    }
}

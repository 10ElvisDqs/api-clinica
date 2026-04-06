<?php

namespace App\Http\Controllers\Appointment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentAttention;
use App\Models\Patient\Patient;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

use Illuminate\Support\Facades\Mail;

class AppointmentAttentioncontroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $appointment = Appointment::findOrFail($request->appointment_id);

        $appointment_attention = $appointment->attention;

        $request->request->add(["receta_medica" => json_encode($request->medical)]);
        if($appointment_attention){
            $this->authorize('view',$appointment_attention);
            //dd("Paso 2");
            if(!$appointment->date_attention){
                $appointment->update(["status" => 2,
                "date_attention" => now()]);
            }
            $appointment_attention->update($request->all());
        }else{
            //dd("Paso 1");
            $this->authorize('viewAppointment',$appointment);
            AppointmentAttention::create($request->all());
            date_default_timezone_set('America/Lima');
            $appointment->update(["status" => 2,
            "date_attention" => now()]);
        }
        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        //dd($appointment);

        $appointment_attention = $appointment->attention;
        if($appointment_attention){
            $this->authorize('view',$appointment_attention);
            return response()->json([
                "appointment_attention" => [
                    "id" => $appointment_attention->id,
                    "description" => $appointment_attention->description,
                    "receta_medica" => $appointment_attention->receta_medica ? json_decode($appointment_attention->receta_medica) : [],
                    "created_at" => $appointment_attention->created_at->format("Y-m-d h:i A"),
                ]
            ]);

        }else{
            return response()->json([
                "appointment_attention" => [
                    "id" => NULL,
                    "description" => NULL,
                    "receta_medica" => [],
                    "created_at" => NULL,
                ]
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function receta(Request $request){
        // Validar los parámetros de entrada
        $request->validate([
            'appointment_id' => 'required|integer',
            'patient_id' => 'required|integer',
        ]);

        // Obtener los registros basados en appointment_id y patient_id
        $appointmentAttentions = AppointmentAttention::where('appointment_id', $request->appointment_id)
                                                    ->where('patient_id', $request->patient_id)
                                                    ->get(['id', 'appointment_id', 'patient_id', 'description', 'receta_medica', 'created_at']);

        // Obtener el paciente por su ID
        $patient = Patient::findOrFail($request->patient_id);

        // Generar los datos de receta_medica para la vista
        $medicamentos = [];
        foreach ($appointmentAttentions as $appointment) {
            $recetaMedica = json_decode($appointment->receta_medica, true);
            foreach ($recetaMedica as $medicamento) {
                $medicamentos[] = [
                    'name_medical' => $medicamento['name_medical'],
                    'uso' => $medicamento['uso'],
                ];
            }
        }

        // Generar el PDF
        $pdf = PDF::setPaper('letter', 'landscape')->loadView('ReporteAppointment.receta', compact('appointmentAttentions', 'medicamentos'));

        // Obtener el contenido del PDF como datos brutos
        $pdfData = $pdf->output();

        // Enviar el correo electrónico con el archivo adjunto del PDF
        Mail::send([], [], function ($message) use ($pdfData, $patient) {
            $message->to($patient->email)
                    ->subject('Receta Médica')
                    ->attachData($pdfData, 'receta.pdf', [
                        'mime' => 'application/pdf',
                    ])
                    ->text('Adjunto encontrarás la receta médica.'); // Cuerpo del correo electrónico en texto plano
        });

        // Retornar el PDF como respuesta de descarga
        return $pdf->download('receta.pdf');
    }
}

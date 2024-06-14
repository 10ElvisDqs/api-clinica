<?php

namespace App\Http\Controllers\Appointment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentAttention;

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
            if(!$appointment->date_attention){
                $appointment->update(["status" => 2,
                "date_attention" => now()]);
            }
            $appointment_attention->update($request->all());
        }else{
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

        $appointment_attention = $appointment->attention;

        if($appointment_attention){

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
}

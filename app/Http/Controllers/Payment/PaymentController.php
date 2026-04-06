<?php

namespace App\Http\Controllers\Payment;

use App\DTOs\Payment\PaymentData;
use App\Http\Controllers\Controller;
use App\Models\Doctor\Specialitie;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Services\Payment\PaymentServiceFactory;
use Illuminate\Http\Request;

/**
 * Single Responsibility: solo orquesta la solicitud de pago.
 * El procesamiento real lo delega a la estrategia correspondiente.
 */
class PaymentController extends Controller
{
    public function createSession(Request $request)
    {
        $request->validate([
            'method'                       => 'required|in:' . implode(',', PaymentServiceFactory::availableMethods()),
            'specialitie_id'               => 'required|exists:specialities,id',
            'doctor_id'                    => 'required|exists:users,id',
            'doctor_schedule_join_hour_id' => 'required|exists:doctor_schedule_join_hours,id',
            'date_appointment'             => 'required|date',
        ]);

        $user      = auth('api')->user();
        $patient   = Patient::where('user_id', $user->id)->firstOrFail();
        $specialty = Specialitie::findOrFail($request->specialitie_id);
        $doctor    = User::findOrFail($request->doctor_id);

        $paymentData = new PaymentData(
            patientId:                $patient->id,
            userId:                   $user->id,
            doctorId:                 $doctor->id,
            specialitieId:            $specialty->id,
            doctorScheduleJoinHourId: $request->doctor_schedule_join_hour_id,
            dateAppointment:          $request->date_appointment,
            amount:                   $specialty->price,
            specialityName:           $specialty->name,
            doctorName:               'Dr. ' . $doctor->name . ' ' . $doctor->surname,
            userEmail:                $user->email,
            frontendUrl:              rtrim(config('app.frontend_url', 'http://192.168.100.9:8080'), '/'),
        );

        $result = PaymentServiceFactory::make($request->method)->process($paymentData);

        if (! $result->success) {
            return response()->json(['message' => $result->message], 422);
        }

        // Stripe Embedded → devolver client_secret para montar el form en la página
        if ($result->clientSecret) {
            return response()->json([
                'client_secret'   => $result->clientSecret,
                'publishable_key' => config('cashier.key'),
            ]);
        }

        // Billing → cita creada directamente
        return response()->json([
            'appointment_id' => $result->appointmentId,
            'message'        => $result->message,
        ], 201);
    }
}

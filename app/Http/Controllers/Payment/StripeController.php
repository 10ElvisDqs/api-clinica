<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentPay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * Single Responsibility: solo maneja el webhook de Stripe.
 * La lógica de checkout fue movida a PaymentController + StripePaymentService.
 */
class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('cashier.secret'));
    }

    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('cashier.webhook.secret');

        try {
            $event = $secret
                ? Webhook::constructEvent($payload, $sigHeader, $secret)
                : json_decode($payload);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($event->data->object);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        if (Appointment::where('stripe_session_id', $session->id)->exists()) {
            Log::info('Stripe webhook: cita ya procesada', ['session_id' => $session->id]);
            return;
        }

        $meta = $session->metadata;

        $appointment = Appointment::create([
            'doctor_id'                    => $meta->doctor_id,
            'patient_id'                   => $meta->patient_id,
            'specialitie_id'               => $meta->specialitie_id,
            'doctor_schedule_join_hour_id' => $meta->doctor_schedule_join_hour_id,
            'date_appointment'             => $meta->date_appointment,
            'user_id'                      => $meta->user_id,
            'amount'                       => $meta->amount,
            'status'                       => 1, // pendiente de atención
            'status_pay'                   => 2, // pagado con Stripe
            'stripe_session_id'            => $session->id,
        ]);

        AppointmentPay::create([
            'appointment_id' => $appointment->id,
            'amount'         => $meta->amount,
            'method_payment' => 'STRIPE',
        ]);

        Log::info('Cita creada por Stripe webhook', ['appointment_id' => $appointment->id]);
    }
}

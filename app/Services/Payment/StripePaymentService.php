<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentStrategyInterface;
use App\DTOs\Payment\PaymentData;
use App\DTOs\Payment\PaymentResult;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

/**
 * Estrategia Stripe: redirige al checkout hosteado de Stripe.
 * Moneda: BOB (boliviano).
 */
class StripePaymentService implements PaymentStrategyInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('cashier.secret'));
    }

    public function process(PaymentData $data): PaymentResult
    {
        $session = StripeSession::create([
            'ui_mode'              => 'embedded',           // sin salir de la página
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'bob',
                    'product_data' => [
                        'name'        => 'Consulta: ' . $data->specialityName,
                        'description' => $data->doctorName . ' — ' . $data->dateAppointment,
                    ],
                    'unit_amount' => (int) ($data->amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode'           => 'payment',
            'return_url'     => $data->frontendUrl . '/patient/dashboard?payment=success&session_id={CHECKOUT_SESSION_ID}',
            'customer_email' => $data->userEmail,
            'metadata'       => [
                'patient_id'                   => $data->patientId,
                'doctor_id'                    => $data->doctorId,
                'specialitie_id'               => $data->specialitieId,
                'doctor_schedule_join_hour_id' => $data->doctorScheduleJoinHourId,
                'date_appointment'             => $data->dateAppointment,
                'user_id'                      => $data->userId,
                'amount'                       => $data->amount,
            ],
        ]);

        return new PaymentResult(
            success:      true,
            clientSecret: $session->client_secret,  // el frontend lo usa para montar el form
        );
    }
}

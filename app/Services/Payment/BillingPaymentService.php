<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentStrategyInterface;
use App\DTOs\Payment\PaymentData;
use App\DTOs\Payment\PaymentResult;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentPay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Estrategia Billing: crea la cita al instante con pago pendiente.
 * El paciente paga en recepción el día de la consulta.
 * status_pay = 1 → pendiente de pago (billing)
 */
class BillingPaymentService implements PaymentStrategyInterface
{
    public function process(PaymentData $data): PaymentResult
    {
        DB::beginTransaction();

        try {
            $appointment = Appointment::create([
                'doctor_id'                    => $data->doctorId,
                'patient_id'                   => $data->patientId,
                'specialitie_id'               => $data->specialitieId,
                'doctor_schedule_join_hour_id' => $data->doctorScheduleJoinHourId,
                'date_appointment'             => $data->dateAppointment,
                'user_id'                      => $data->userId,
                'amount'                       => $data->amount,
                'status'                       => 1, // pendiente de atención
                'status_pay'                   => 1, // pendiente de pago en clínica
            ]);

            AppointmentPay::create([
                'appointment_id' => $appointment->id,
                'amount'         => $data->amount,
                'method_payment' => 'BILLING',
            ]);

            DB::commit();

            Log::info('Cita registrada por Billing', ['appointment_id' => $appointment->id]);

            return new PaymentResult(
                success:       true,
                message:       'Cita registrada correctamente. Presente este comprobante en recepción y realice el pago el día de su consulta.',
                appointmentId: $appointment->id,
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('BillingPaymentService error: ' . $e->getMessage());

            return new PaymentResult(
                success: false,
                message: 'No se pudo registrar la cita. Intente nuevamente.',
            );
        }
    }
}

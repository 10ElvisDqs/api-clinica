<?php

namespace App\DTOs\Payment;

/**
 * Value Object — datos necesarios para procesar cualquier método de pago.
 * Inmutable: todos los campos son readonly.
 */
final class PaymentData
{
    public function __construct(
        public readonly int    $patientId,
        public readonly int    $userId,
        public readonly int    $doctorId,
        public readonly int    $specialitieId,
        public readonly int    $doctorScheduleJoinHourId,
        public readonly string $dateAppointment,
        public readonly float  $amount,
        public readonly string $specialityName,
        public readonly string $doctorName,
        public readonly string $userEmail,
        public readonly string $frontendUrl = '',
    ) {}
}

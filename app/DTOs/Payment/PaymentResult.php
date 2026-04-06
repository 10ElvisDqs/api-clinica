<?php

namespace App\DTOs\Payment;

/**
 * Value Object — resultado de procesar un pago.
 * Inmutable: todos los campos son readonly.
 */
final class PaymentResult
{
    public function __construct(
        public readonly bool    $success,
        public readonly string  $message        = '',
        public readonly ?string $redirectUrl    = null,   // checkout redirect (legacy)
        public readonly ?string $clientSecret   = null,   // embedded checkout
        public readonly ?int    $appointmentId  = null,
    ) {}
}

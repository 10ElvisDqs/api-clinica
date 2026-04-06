<?php

namespace App\Contracts\Payment;

use App\DTOs\Payment\PaymentData;
use App\DTOs\Payment\PaymentResult;

/**
 * Contrato (Interface Segregation Principle) para cualquier método de pago.
 * Cada implementación es independiente y reemplazable (Liskov Substitution).
 */
interface PaymentStrategyInterface
{
    public function process(PaymentData $data): PaymentResult;
}

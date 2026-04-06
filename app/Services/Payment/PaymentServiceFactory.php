<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentStrategyInterface;

/**
 * Factory + Open/Closed Principle:
 * Para agregar un nuevo método de pago, solo se agrega una entrada en $strategies
 * sin tocar el controlador ni la lógica existente.
 */
class PaymentServiceFactory
{
    private static array $strategies = [
        'stripe'  => StripePaymentService::class,
        'billing' => BillingPaymentService::class,
    ];

    public static function make(string $method): PaymentStrategyInterface
    {
        $class = self::$strategies[$method]
            ?? throw new \InvalidArgumentException("Método de pago no soportado: {$method}");

        return app($class);
    }

    /** Devuelve los métodos registrados (útil para validación). */
    public static function availableMethods(): array
    {
        return array_keys(self::$strategies);
    }
}

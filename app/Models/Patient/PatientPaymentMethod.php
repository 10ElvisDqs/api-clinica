<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientPaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'type',
        'label',
        'bank_name',
        'account_number',
        'account_holder',
        'phone_number',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /** Oculta el número completo — devuelve solo los últimos 4 dígitos. */
    public function getMaskedAccountAttribute(): ?string
    {
        if (! $this->account_number) return null;
        return '••••' . substr($this->account_number, -4);
    }

    /** Íconos por tipo (útil para el frontend). */
    public static function iconFor(string $type): string
    {
        return match ($type) {
            'bank_transfer' => 'icon-briefcase',
            'tigo_money'    => 'icon-smartphone',
            'qr'            => 'icon-grid',
            default         => 'icon-dollar-sign',
        };
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

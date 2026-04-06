<?php

namespace App\Models\Seguimiento;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seguimiento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id', 'doctor_id', 'user_id',
        'appointment_attention_id', 'fecha_seguimiento',
        'motivo', 'observaciones', 'estado',
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('America/Lima')->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('America/Lima')->format('Y-m-d H:i:s');
    }

    public function patient()
    {
        return $this->belongsTo(\App\Models\Patient\Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(\App\Models\User::class, 'doctor_id');
    }

    public function appointmentAttention()
    {
        return $this->belongsTo(\App\Models\Appointment\AppointmentAttention::class);
    }

    public function scopeFilterAdvance($query, $search = null, $estado = null)
    {
        if ($search) {
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(name,' ',surname)"), 'LIKE', '%' . $search . '%')
                  ->orWhere('n_document', 'LIKE', '%' . $search . '%');
            });
        }
        if ($estado) {
            $query->where('estado', $estado);
        }
        return $query;
    }
}

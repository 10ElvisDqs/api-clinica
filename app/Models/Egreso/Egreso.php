<?php

namespace App\Models\Egreso;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Egreso extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ingreso_id', 'patient_id', 'doctor_id', 'user_id',
        'fecha_egreso', 'tipo_egreso', 'diagnostico_final',
        'indicaciones', 'observaciones',
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('America/Lima')->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('America/Lima')->format('Y-m-d H:i:s');
    }

    public function ingreso()
    {
        return $this->belongsTo(\App\Models\Ingreso\Ingreso::class);
    }

    public function patient()
    {
        return $this->belongsTo(\App\Models\Patient\Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(\App\Models\User::class, 'doctor_id');
    }

    public function scopeFilterAdvance($query, $search = null, $tipo = null)
    {
        if ($search) {
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where(\Illuminate\Support\Facades\DB::raw("CONCAT(name,' ',surname)"), 'LIKE', '%' . $search . '%')
                  ->orWhere('n_document', 'LIKE', '%' . $search . '%');
            });
        }
        if ($tipo) {
            $query->where('tipo_egreso', $tipo);
        }
        return $query;
    }
}

<?php

namespace App\Models\Ingreso;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ingreso extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id', 'doctor_id', 'user_id',
        'fecha_ingreso', 'motivo', 'sala', 'cama',
        'estado', 'observaciones',
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

    public function registrador()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function egreso()
    {
        return $this->hasOne(\App\Models\Egreso\Egreso::class);
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

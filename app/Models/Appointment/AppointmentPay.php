<?php

namespace App\Models\Appointment;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentPay extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        "appointment_id",
        "amount",
        "method_payment",
    ];

    public function setCreatedAtAttribute($value)
    {
    	date_default_timezone_set('America/Lima');
        $this->attributes["created_at"]= Carbon::now();
    }

    public function setUpdatedAtAttribute($value)
    {
    	date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"]= Carbon::now();
    }

    public function appointment(){
        return $this->belongsTo(Appointment::class);
    }
}

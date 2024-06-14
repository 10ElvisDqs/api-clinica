<?php

namespace App\Models\Patient;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientPerson extends Model
{
    use HasFactory;

    protected $fillable = [
        "patient_id",
        "name_companion",
        "surname_companion",
        "mobile_companion",
        "relationship_companion",
        "name_responsible",
        "surname_responsible",
        "mobile_responsible",
        "relationship_responsible",
    ];

    protected $table = "patient_persons";

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
}

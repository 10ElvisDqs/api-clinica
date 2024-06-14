<?php

namespace App\Http\Resources\Patient;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "name" => $this->resource->name,
            "surname" => $this->resource->surname,
            "full_name" => $this->resource->name . ' ' .$this->resource->surname,
            "n_document" => $this->resource->n_document,
            "mobile" => $this->resource->mobile,
            "email" => $this->resource->email,
            "avatar" => $this->resource->avatar ? env("APP_URL")."storage/".$this->resource->avatar : NULL,
            "birth_date" => $this->resource->birth_date ? Carbon::parse($this->resource->birth_date)->format("Y/m/d") : NULL,
            "gender" => $this->resource->gender,
            "education" => $this->resource->education,
            "address" => $this->resource->address,
            "antecedent_family" => $this->resource->antecedent_family,
            "antecedent_personal" => $this->resource->antecedent_personal,
            "antecedent_allergic" => $this->resource->antecedent_allergic,
            "current_disease" => $this->resource->current_disease,
            "ta" => $this->resource->ta,
            "temperatura" => $this->resource->temperatura,
            "fc" => $this->resource->fc,
            "fr" => $this->resource->fr,
            "peso" => $this->resource->peso,
            "person" => $this->resource->person ? [
                "id" => $this->resource->person->id,
                "patient_id" => $this->resource->person->patient_id,
                "name_companion" => $this->resource->person->name_companion,
                "surname_companion" => $this->resource->person->surname_companion,
                "mobile_companion" => $this->resource->person->mobile_companion,
                "relationship_companion" => $this->resource->person->relationship_companion,
                "name_responsible" => $this->resource->person->name_responsible,
                "surname_responsible" => $this->resource->person->surname_responsible,
                "mobile_responsible" => $this->resource->person->mobile_responsible,
                "relationship_responsible" => $this->resource->person->relationship_responsible,
            ]: NULL,
            "created_at" => $this->resource->created_at->format("Y-m-d h:i A"),
        ];
    }
}

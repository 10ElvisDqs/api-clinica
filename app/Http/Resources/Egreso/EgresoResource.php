<?php

namespace App\Http\Resources\Egreso;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EgresoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'ingreso_id'        => $this->ingreso_id,
            'patient_id'        => $this->patient_id,
            'doctor_id'         => $this->doctor_id,
            'user_id'           => $this->user_id,
            'fecha_egreso'      => $this->fecha_egreso,
            'tipo_egreso'       => $this->tipo_egreso,
            'diagnostico_final' => $this->diagnostico_final,
            'indicaciones'      => $this->indicaciones,
            'observaciones'     => $this->observaciones,
            'created_at'        => $this->created_at,
            'patient'           => $this->patient ? [
                'id'            => $this->patient->id,
                'name'          => $this->patient->name,
                'surname'       => $this->patient->surname,
                'full_name'     => $this->patient->name . ' ' . $this->patient->surname,
                'n_document'    => $this->patient->n_document,
            ] : null,
            'doctor'            => $this->doctor ? [
                'id'            => $this->doctor->id,
                'name'          => $this->doctor->name,
                'surname'       => $this->doctor->surname,
                'full_name'     => $this->doctor->name . ' ' . $this->doctor->surname,
            ] : null,
            'ingreso'           => $this->ingreso ? [
                'id'            => $this->ingreso->id,
                'fecha_ingreso' => $this->ingreso->fecha_ingreso,
                'sala'          => $this->ingreso->sala,
                'cama'          => $this->ingreso->cama,
            ] : null,
        ];
    }
}

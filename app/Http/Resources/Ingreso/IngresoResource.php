<?php

namespace App\Http\Resources\Ingreso;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngresoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'patient_id'    => $this->patient_id,
            'doctor_id'     => $this->doctor_id,
            'user_id'       => $this->user_id,
            'fecha_ingreso' => $this->fecha_ingreso,
            'motivo'        => $this->motivo,
            'sala'          => $this->sala,
            'cama'          => $this->cama,
            'estado'        => $this->estado,
            'observaciones' => $this->observaciones,
            'created_at'    => $this->created_at,
            'patient'       => $this->patient ? [
                'id'         => $this->patient->id,
                'name'       => $this->patient->name,
                'surname'    => $this->patient->surname,
                'full_name'  => $this->patient->name . ' ' . $this->patient->surname,
                'n_document' => $this->patient->n_document,
                'mobile'     => $this->patient->mobile,
            ] : null,
            'doctor'        => $this->doctor ? [
                'id'         => $this->doctor->id,
                'name'       => $this->doctor->name,
                'surname'    => $this->doctor->surname,
                'full_name'  => $this->doctor->name . ' ' . $this->doctor->surname,
            ] : null,
            'egreso'        => $this->egreso ? [
                'id'           => $this->egreso->id,
                'fecha_egreso' => $this->egreso->fecha_egreso,
                'tipo_egreso'  => $this->egreso->tipo_egreso,
            ] : null,
        ];
    }
}

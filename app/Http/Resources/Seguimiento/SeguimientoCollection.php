<?php

namespace App\Http\Resources\Seguimiento;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SeguimientoCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return ['data' => SeguimientoResource::collection($this->collection)];
    }
}

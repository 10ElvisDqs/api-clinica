<?php

namespace App\Http\Resources\Ingreso;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class IngresoCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return ['data' => IngresoResource::collection($this->collection)];
    }
}

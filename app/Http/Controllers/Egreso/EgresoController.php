<?php

namespace App\Http\Controllers\Egreso;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Egreso\Egreso;
use App\Models\Ingreso\Ingreso;
use App\Http\Controllers\Controller;
use App\Http\Resources\Egreso\EgresoResource;
use App\Http\Resources\Egreso\EgresoCollection;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class EgresoController extends Controller
{
    /** Listado paginado con filtros */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Egreso::class);

        $search = $request->search;
        $tipo   = $request->tipo_egreso;

        $egresos = Egreso::with(['patient', 'doctor', 'ingreso'])
            ->filterAdvance($search, $tipo)
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json([
            'total'   => $egresos->total(),
            'egresos' => EgresoCollection::make($egresos),
        ]);
    }

    /** Ingresos activos para el selector al registrar egreso */
    public function config()
    {
        $this->authorize('viewAny', Egreso::class);

        $ingresos = Ingreso::with(['patient', 'doctor'])
            ->where('estado', 1)
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($i) => [
                'id'            => $i->id,
                'fecha_ingreso' => $i->fecha_ingreso,
                'sala'          => $i->sala,
                'cama'          => $i->cama,
                'patient'       => $i->patient ? [
                    'full_name'   => $i->patient->name . ' ' . $i->patient->surname,
                    'n_document'  => $i->patient->n_document,
                ] : null,
                'doctor'        => $i->doctor ? [
                    'full_name'   => $i->doctor->name . ' ' . $i->doctor->surname,
                ] : null,
            ]);

        return response()->json(['ingresos' => $ingresos]);
    }

    /** Crear egreso y cerrar el ingreso correspondiente */
    public function store(Request $request)
    {
        $this->authorize('create', Egreso::class);

        $request->validate([
            'ingreso_id'        => 'required|exists:ingresos,id|unique:egresos,ingreso_id',
            'fecha_egreso'      => 'required|date',
            'tipo_egreso'       => 'required|in:alta_medica,referido,voluntario,fallecido',
            'diagnostico_final' => 'required|string|max:2000',
        ]);

        $ingreso = Ingreso::findOrFail($request->ingreso_id);

        $egreso = Egreso::create([
            'ingreso_id'        => $request->ingreso_id,
            'patient_id'        => $ingreso->patient_id,
            'doctor_id'         => $ingreso->doctor_id,
            'user_id'           => auth('api')->id(),
            'fecha_egreso'      => $request->fecha_egreso,
            'tipo_egreso'       => $request->tipo_egreso,
            'diagnostico_final' => $request->diagnostico_final,
            'indicaciones'      => $request->indicaciones,
            'observaciones'     => $request->observaciones,
        ]);

        // Marcar el ingreso como egresado
        $ingreso->update(['estado' => 2]);

        return response()->json([
            'message' => 'Egreso registrado correctamente.',
            'egreso'  => EgresoResource::make($egreso->load(['patient', 'doctor', 'ingreso'])),
        ], 201);
    }

    /** Detalle de un egreso */
    public function show($id)
    {
        $egreso = Egreso::with(['patient', 'doctor', 'ingreso'])->findOrFail($id);
        $this->authorize('update', $egreso);

        return response()->json([
            'egreso' => EgresoResource::make($egreso),
        ]);
    }

    /** Actualizar egreso */
    public function update(Request $request, $id)
    {
        $egreso = Egreso::findOrFail($id);
        $this->authorize('update', $egreso);

        $request->validate([
            'fecha_egreso'      => 'required|date',
            'tipo_egreso'       => 'required|in:alta_medica,referido,voluntario,fallecido',
            'diagnostico_final' => 'required|string|max:2000',
        ]);

        $egreso->update([
            'fecha_egreso'      => $request->fecha_egreso,
            'tipo_egreso'       => $request->tipo_egreso,
            'diagnostico_final' => $request->diagnostico_final,
            'indicaciones'      => $request->indicaciones,
            'observaciones'     => $request->observaciones,
        ]);

        return response()->json([
            'message' => 'Egreso actualizado correctamente.',
            'egreso'  => EgresoResource::make($egreso->fresh()->load(['patient', 'doctor', 'ingreso'])),
        ]);
    }

    /** Eliminar egreso y reactivar el ingreso */
    public function destroy($id)
    {
        $egreso = Egreso::findOrFail($id);
        $this->authorize('delete', $egreso);

        // Reactivar el ingreso al deshacer el egreso
        Ingreso::where('id', $egreso->ingreso_id)->update(['estado' => 1]);
        $egreso->delete();

        return response()->json(['message' => 'Egreso eliminado correctamente.']);
    }

    /** Reporte PDF */
    public function reporte(Request $request)
    {
        $this->authorize('viewAny', Egreso::class);

        $egresos = Egreso::with(['patient', 'doctor', 'ingreso'])
            ->filterAdvance($request->search, $request->tipo_egreso)
            ->orderBy('id', 'desc')
            ->get();

        $resultado = EgresoCollection::make($egresos)->toArray($request);
        $pdf = PDF::loadView('ReporteEgreso.pdf', ['resultado' => $resultado]);

        return $pdf->download('reporte_egresos.pdf');
    }
}

<?php

namespace App\Http\Controllers\Ingreso;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ingreso\Ingreso;
use App\Models\Patient\Patient;
use App\Http\Controllers\Controller;
use App\Http\Resources\Ingreso\IngresoResource;
use App\Http\Resources\Ingreso\IngresoCollection;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class IngresoController extends Controller
{
    /** Listado paginado con filtros */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Ingreso::class);

        $search = $request->search;
        $estado = $request->estado;

        $ingresos = Ingreso::with(['patient', 'doctor', 'egreso'])
            ->filterAdvance($search, $estado)
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json([
            'total'    => $ingresos->total(),
            'ingresos' => IngresoCollection::make($ingresos),
        ]);
    }

    /** Datos para los selectores del formulario */
    public function config()
    {
        $this->authorize('viewAny', Ingreso::class);

        $doctors  = User::role('DOCTOR')->select('id', 'name', 'surname')->get()
            ->map(fn($d) => ['id' => $d->id, 'full_name' => $d->name . ' ' . $d->surname]);

        $patients = Patient::select('id', 'name', 'surname', 'n_document')->get()
            ->map(fn($p) => ['id' => $p->id, 'full_name' => $p->name . ' ' . $p->surname, 'n_document' => $p->n_document]);

        return response()->json([
            'doctors'  => $doctors,
            'patients' => $patients,
        ]);
    }

    /** Crear nuevo ingreso */
    public function store(Request $request)
    {
        $this->authorize('create', Ingreso::class);

        $request->validate([
            'patient_id'    => 'required|exists:patients,id',
            'doctor_id'     => 'required|exists:users,id',
            'fecha_ingreso' => 'required|date',
            'motivo'        => 'required|string|max:1000',
        ]);

        $ingreso = Ingreso::create([
            'patient_id'    => $request->patient_id,
            'doctor_id'     => $request->doctor_id,
            'user_id'       => auth('api')->id(),
            'fecha_ingreso' => $request->fecha_ingreso,
            'motivo'        => $request->motivo,
            'sala'          => $request->sala,
            'cama'          => $request->cama,
            'estado'        => 1,
            'observaciones' => $request->observaciones,
        ]);

        return response()->json([
            'message' => 'Ingreso registrado correctamente.',
            'ingreso' => IngresoResource::make($ingreso->load(['patient', 'doctor'])),
        ], 201);
    }

    /** Detalle de un ingreso */
    public function show($id)
    {
        $ingreso = Ingreso::with(['patient', 'doctor', 'egreso'])->findOrFail($id);
        $this->authorize('update', $ingreso);

        return response()->json([
            'ingreso' => IngresoResource::make($ingreso),
        ]);
    }

    /** Actualizar ingreso */
    public function update(Request $request, $id)
    {
        $ingreso = Ingreso::findOrFail($id);
        $this->authorize('update', $ingreso);

        $request->validate([
            'fecha_ingreso' => 'required|date',
            'motivo'        => 'required|string|max:1000',
        ]);

        $ingreso->update([
            'doctor_id'     => $request->doctor_id ?? $ingreso->doctor_id,
            'fecha_ingreso' => $request->fecha_ingreso,
            'motivo'        => $request->motivo,
            'sala'          => $request->sala,
            'cama'          => $request->cama,
            'observaciones' => $request->observaciones,
        ]);

        return response()->json([
            'message' => 'Ingreso actualizado correctamente.',
            'ingreso' => IngresoResource::make($ingreso->fresh()->load(['patient', 'doctor'])),
        ]);
    }

    /** Eliminar ingreso (soft delete) */
    public function destroy($id)
    {
        $ingreso = Ingreso::findOrFail($id);
        $this->authorize('delete', $ingreso);
        $ingreso->delete();

        return response()->json(['message' => 'Ingreso eliminado correctamente.']);
    }

    /** Reporte PDF */
    public function reporte(Request $request)
    {
        $this->authorize('viewAny', Ingreso::class);

        $ingresos = Ingreso::with(['patient', 'doctor', 'egreso'])
            ->filterAdvance($request->search, $request->estado)
            ->orderBy('id', 'desc')
            ->get();

        $resultado = IngresoCollection::make($ingresos)->toArray($request);
        $pdf = PDF::loadView('ReporteIngreso.pdf', ['resultado' => $resultado]);

        return $pdf->download('reporte_ingresos.pdf');
    }
}

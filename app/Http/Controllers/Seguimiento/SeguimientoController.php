<?php

namespace App\Http\Controllers\Seguimiento;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Patient\Patient;
use App\Models\Seguimiento\Seguimiento;
use App\Http\Controllers\Controller;
use App\Http\Resources\Seguimiento\SeguimientoResource;
use App\Http\Resources\Seguimiento\SeguimientoCollection;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class SeguimientoController extends Controller
{
    /** Listado paginado con filtros */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Seguimiento::class);

        $search = $request->search;
        $estado = $request->estado;

        $query = Seguimiento::with(['patient', 'doctor']);

        // Si el usuario es DOCTOR, solo ve sus propios seguimientos
        if (auth('api')->user()->hasRole('DOCTOR')) {
            $query->where('doctor_id', auth('api')->id());
        }

        $seguimientos = $query->filterAdvance($search, $estado)
            ->orderBy('fecha_seguimiento', 'asc')
            ->paginate(20);

        return response()->json([
            'total'         => $seguimientos->total(),
            'seguimientos'  => SeguimientoCollection::make($seguimientos),
        ]);
    }

    /** Datos para los selectores del formulario */
    public function config()
    {
        $this->authorize('viewAny', Seguimiento::class);

        $doctors  = User::role('DOCTOR')->select('id', 'name', 'surname')->get()
            ->map(fn($d) => ['id' => $d->id, 'full_name' => $d->name . ' ' . $d->surname]);

        $patients = Patient::select('id', 'name', 'surname', 'n_document')->get()
            ->map(fn($p) => [
                'id'         => $p->id,
                'full_name'  => $p->name . ' ' . $p->surname,
                'n_document' => $p->n_document,
            ]);

        return response()->json([
            'doctors'  => $doctors,
            'patients' => $patients,
        ]);
    }

    /** Crear seguimiento */
    public function store(Request $request)
    {
        $this->authorize('create', Seguimiento::class);

        $request->validate([
            'patient_id'        => 'required|exists:patients,id',
            'doctor_id'         => 'required|exists:users,id',
            'fecha_seguimiento' => 'required|date',
            'motivo'            => 'required|string|max:1000',
        ]);

        $seguimiento = Seguimiento::create([
            'patient_id'               => $request->patient_id,
            'doctor_id'                => $request->doctor_id,
            'user_id'                  => auth('api')->id(),
            'appointment_attention_id' => $request->appointment_attention_id,
            'fecha_seguimiento'        => $request->fecha_seguimiento,
            'motivo'                   => $request->motivo,
            'observaciones'            => $request->observaciones,
            'estado'                   => 1,
        ]);

        return response()->json([
            'message'      => 'Seguimiento registrado correctamente.',
            'seguimiento'  => SeguimientoResource::make($seguimiento->load(['patient', 'doctor'])),
        ], 201);
    }

    /** Detalle de un seguimiento */
    public function show($id)
    {
        $seguimiento = Seguimiento::with(['patient', 'doctor'])->findOrFail($id);
        $this->authorize('update', $seguimiento);

        return response()->json([
            'seguimiento' => SeguimientoResource::make($seguimiento),
        ]);
    }

    /** Actualizar seguimiento */
    public function update(Request $request, $id)
    {
        $seguimiento = Seguimiento::findOrFail($id);
        $this->authorize('update', $seguimiento);

        $request->validate([
            'fecha_seguimiento' => 'required|date',
            'motivo'            => 'required|string|max:1000',
            'estado'            => 'required|in:1,2,3',
        ]);

        $seguimiento->update([
            'doctor_id'         => $request->doctor_id ?? $seguimiento->doctor_id,
            'fecha_seguimiento' => $request->fecha_seguimiento,
            'motivo'            => $request->motivo,
            'observaciones'     => $request->observaciones,
            'estado'            => $request->estado,
        ]);

        return response()->json([
            'message'     => 'Seguimiento actualizado correctamente.',
            'seguimiento' => SeguimientoResource::make($seguimiento->fresh()->load(['patient', 'doctor'])),
        ]);
    }

    /** Eliminar seguimiento (soft delete) */
    public function destroy($id)
    {
        $seguimiento = Seguimiento::findOrFail($id);
        $this->authorize('delete', $seguimiento);
        $seguimiento->delete();

        return response()->json(['message' => 'Seguimiento eliminado correctamente.']);
    }

    /** Reporte PDF */
    public function reporte(Request $request)
    {
        $this->authorize('viewAny', Seguimiento::class);

        $query = Seguimiento::with(['patient', 'doctor']);

        if (auth('api')->user()->hasRole('DOCTOR')) {
            $query->where('doctor_id', auth('api')->id());
        }

        $seguimientos = $query->filterAdvance($request->search, $request->estado)
            ->orderBy('fecha_seguimiento', 'asc')
            ->get();

        $resultado = SeguimientoCollection::make($seguimientos)->toArray($request);
        $pdf = PDF::loadView('ReporteSeguimiento.pdf', ['resultado' => $resultado]);

        return $pdf->download('reporte_seguimientos.pdf');
    }
}

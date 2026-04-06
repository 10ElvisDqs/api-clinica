<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\Patient;
use App\Models\Patient\PatientPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientPaymentMethodController extends Controller
{
    private function getPatient(): Patient
    {
        return Patient::where('user_id', auth('api')->id())->firstOrFail();
    }

    public function index()
    {
        $patient = $this->getPatient();

        $methods = PatientPaymentMethod::where('patient_id', $patient->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($m) => $this->format($m));

        return response()->json(['data' => $methods]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'           => 'required|in:bank_transfer,tigo_money,cash,qr',
            'label'          => 'required|string|max:80',
            'bank_name'      => 'nullable|string|max:60',
            'account_number' => 'nullable|string|max:30',
            'account_holder' => 'nullable|string|max:80',
            'phone_number'   => 'nullable|string|max:20',
            'is_default'     => 'boolean',
        ]);

        $patient = $this->getPatient();

        DB::transaction(function () use ($request, $patient) {
            if ($request->boolean('is_default')) {
                PatientPaymentMethod::where('patient_id', $patient->id)
                    ->update(['is_default' => false]);
            }

            PatientPaymentMethod::create([
                'patient_id'     => $patient->id,
                'type'           => $request->type,
                'label'          => $request->label,
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
                'phone_number'   => $request->phone_number,
                'is_default'     => $request->boolean('is_default'),
            ]);
        });

        return response()->json(['message' => 'Método de pago registrado.'], 201);
    }

    public function setDefault(int $id)
    {
        $patient = $this->getPatient();

        DB::transaction(function () use ($id, $patient) {
            PatientPaymentMethod::where('patient_id', $patient->id)
                ->update(['is_default' => false]);

            PatientPaymentMethod::where('id', $id)
                ->where('patient_id', $patient->id)
                ->update(['is_default' => true]);
        });

        return response()->json(['message' => 'Método predeterminado actualizado.']);
    }

    public function destroy(int $id)
    {
        $patient = $this->getPatient();

        PatientPaymentMethod::where('id', $id)
            ->where('patient_id', $patient->id)
            ->firstOrFail()
            ->delete();

        return response()->json(['message' => 'Método eliminado.']);
    }

    private function format(PatientPaymentMethod $m): array
    {
        return [
            'id'             => $m->id,
            'type'           => $m->type,
            'label'          => $m->label,
            'bank_name'      => $m->bank_name,
            'account_masked' => $m->masked_account,
            'account_holder' => $m->account_holder,
            'phone_number'   => $m->phone_number,
            'is_default'     => $m->is_default,
            'icon'           => PatientPaymentMethod::iconFor($m->type),
        ];
    }
}

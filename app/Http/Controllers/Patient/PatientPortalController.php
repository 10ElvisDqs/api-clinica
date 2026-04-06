<?php

namespace App\Http\Controllers\Patient;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Patient\Patient;
use App\Models\Doctor\Specialitie;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Models\Patient\PatientPerson;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentAttention;
use App\Http\Resources\Patient\PatientResource;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Http\Resources\Appointment\AppointmentCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PatientPortalController extends Controller
{
    // ── PÚBLICO: listado para la landing page ─────────────────────────────

    public function publicDoctors()
    {
        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'like', '%DOCTOR%'))
            ->select('id', 'name', 'surname', 'avatar', 'email')
            ->with(['specialitie:id,name'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($d) => [
                'id'          => $d->id,
                'full_name'   => $d->name . ' ' . $d->surname,
                'avatar'      => $d->avatar ? env('APP_URL') . 'storage/' . $d->avatar : null,
                'specialitie' => $d->specialitie ? $d->specialitie->name : null,
            ]);

        return response()->json(['doctors' => $doctors]);
    }

    public function publicSpecialities()
    {
        $specialities = Specialitie::where('state', 1)
            ->select('id', 'name', 'price')
            ->orderBy('name')
            ->get();

        return response()->json(['specialities' => $specialities]);
    }

    // ── REGISTRO PÚBLICO DEL PACIENTE ─────────────────────────────────────

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:100',
            'surname'    => 'required|string|max:100',
            'email'      => 'required|email',
            'password'   => 'required|min:6',
            'n_document' => 'required|string|max:20',
            'mobile'     => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender'     => 'nullable|string',
            'address'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message'      => 422,
                'message_text' => implode(' | ', $validator->errors()->all()),
            ]);
        }

        // Email ya registrado
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 403, 'message_text' => 'EL CORREO YA ESTÁ REGISTRADO.']);
        }

        // Documento ya vinculado a una cuenta
        $existingPatient = Patient::where('n_document', $request->n_document)->first();
        if ($existingPatient && $existingPatient->user_id) {
            return response()->json(['message' => 403, 'message_text' => 'EL PACIENTE CON ESTE DOCUMENTO YA TIENE UNA CUENTA.']);
        }

        // Convertir género: "M" → 1, "F" → 2, null/otro → 1 (default DB)
        $genderMap = ['M' => 1, 'F' => 2];
        $genderVal = isset($genderMap[$request->gender]) ? $genderMap[$request->gender] : 1;

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $request->name,
                'surname'  => $request->surname,
                'email'    => $request->email,
                'password' => bcrypt($request->password),
            ]);

            // Asignar rol PACIENTE (guard api)
            $role = Role::where('name', 'PACIENTE')->where('guard_name', 'api')->first();
            if ($role) {
                DB::table('model_has_roles')->insert([
                    'role_id'    => $role->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id'   => $user->id,
                ]);
            }

            // Crear o vincular paciente
            if ($existingPatient) {
                $existingPatient->update(['user_id' => $user->id]);
            } else {

                Patient::create([
                    'user_id'    => $user->id,
                    'name'       => $request->name,
                    'surname'    => $request->surname,
                    'email'      => $request->email,
                    'n_document' => $request->n_document,
                    'mobile'     => $request->mobile ?? null,
                    'birth_date' => $request->birth_date
                        ? Carbon::parse($request->birth_date)->format('Y-m-d H:i:s')
                        : null,
                    'gender'     => $genderVal,
                    'address'    => $request->address ?? null,
                ]);
                // Registrar como cliente en Stripe (User tiene Billable)
                $user->createOrGetStripeCustomer();
            }
            DB::commit();
            return response()->json(['message' => 200, 'message_text' => 'Registro exitoso. Ya puedes iniciar sesión.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 500, 'message_text' => 'Error al registrar: ' . $e->getMessage()]);
        }
    }

    // ── PORTAL PROTEGIDO (requiere auth:api + rol PACIENTE) ───────────────

    public function myProfile()
    {
        $user    = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();

        return response()->json([
            'user'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'surname' => $user->surname,
                'email'   => $user->email,
                'avatar'  => $user->avatar ? env('APP_URL') . 'storage/' . $user->avatar : null,
            ],
            'patient' => $patient ? PatientResource::make($patient) : null,
        ]);
    }

    public function myAppointments(Request $request)
    {
        $user    = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();

        if (!$patient) {
            return response()->json(['appointments' => [], 'total' => 0]);
        }

        $appointments = Appointment::where('patient_id', $patient->id)
            ->with(['doctor', 'specialitie', 'doctor_schedule_join_hour.doctor_schedule_hour', 'payments', 'attention'])
            ->orderBy('date_appointment', 'desc')
            ->paginate(10);

        return response()->json([
            'total'        => $appointments->total(),
            'appointments' => AppointmentCollection::make($appointments),
        ]);
    }

    public function myHistory()
    {
        $user    = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();

        if (!$patient) {
            return response()->json(['history' => []]);
        }

        $history = AppointmentAttention::whereHas('appointment', fn($q) => $q->where('patient_id', $patient->id))
            ->with(['appointment.doctor', 'appointment.specialitie'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($att) => [
                'id'             => $att->id,
                'date'           => $att->created_at->format('Y-m-d'),
                'description'    => $att->description,
                'receta_medica'  => $att->receta_medica ? json_decode($att->receta_medica) : [],
                'doctor'         => $att->appointment && $att->appointment->doctor
                    ? $att->appointment->doctor->name . ' ' . $att->appointment->doctor->surname
                    : null,
                'specialitie'    => $att->appointment && $att->appointment->specialitie
                    ? $att->appointment->specialitie->name
                    : null,
            ]);

        return response()->json(['history' => $history]);
    }

    public function mySeguimientos()
    {
        $user    = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();

        if (!$patient) {
            return response()->json(['seguimientos' => []]);
        }

        $seguimientos = \App\Models\Seguimiento\Seguimiento::where('patient_id', $patient->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['seguimientos' => $seguimientos]);
    }
}

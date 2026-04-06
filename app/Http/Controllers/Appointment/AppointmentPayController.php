<?php

namespace App\Http\Controllers\Appointment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentPay;
use App\Http\Resources\Appointment\Pay\AppointmentPayCollection;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class AppointmentPayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny',AppointmentPay::class);
        $specialitie_id = $request->specialitie_id;
        $search_doctor = $request->search_doctor;
        $search_patient = $request->search_patient;
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $user = auth("api")->user();

        $appointments = Appointment::filterAdvancePay($specialitie_id,$search_doctor,$search_patient,$date_start,$date_end,$user)
                        ->orderBy("status_pay","desc")
                        ->paginate(20);

        return response()->json([
            "total" => $appointments->total(),
            "appointments" => AppointmentPayCollection::make($appointments),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sum_total_pays = AppointmentPay::where("appointment_id",$request->appointment_id)->sum("amount");
        if(($sum_total_pays + $request->amount) > $request->appointment_total){
            return response()->json([
                "message" => 403,
                "message_text" => "EL MONTO QUE SE QUIERE REGISTRAR SUPERA AL COSTO DE LA CITA MEDICA",
            ]);
        }

        $apppointment = Appointment::findOrFail($request->appointment_id);

        $this->authorize('addPayment',$apppointment);

        $appointment_pay = AppointmentPay::create([
            "appointment_id" => $request->appointment_id,
            "amount" =>  $request->amount,
            "method_payment"=>  $request->method_payment,
        ]);


        $is_total_payment = false;
        if(($apppointment->amount) == ($sum_total_pays + $request->amount)){
            $apppointment->update(["status_pay" => 1]);
            $is_total_payment = true;
        }
        return response()->json([
            "message" => 200,
            "appointment_pay" => [
                "is_total_payment" => $is_total_payment,
                "id" => $appointment_pay->id,
                "appointment_id" => $appointment_pay->appointment_id,
                "amount" => $appointment_pay->amount,
                "method_payment" => $appointment_pay->method_payment,
                "created_at" => $appointment_pay->created_at->format("Y-m-d h:i A"),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appointment_pay = Appointment::findOrFail($id);
        dd($appointment_pay);
        // $this->authorize('view', $appointment_pay);
        return response()->json([
            "message" => 200,
            "appointment_pay" => [
                "id" => $appointment_pay->id,
                "appointment_id" => $appointment_pay->appointment_id,
                "amount" => $appointment_pay->amount,
                "method_payment" => $appointment_pay->method_payment,
                "created_at" => $appointment_pay->created_at->format("Y-m-d h:i A"),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $sum_total_pays = AppointmentPay::where("appointment_id",$request->appointment_id)->sum("amount");
        $appointment_pay = AppointmentPay::findOrFail($id);

        $old_amount = $appointment_pay->amount;
        $new_amount = $request->amount;
        $this->authorize('view',$appointment_pay);
        // dd("Validacion Perfecta");
        // 100
        // 50
        // 30 -> 80
        // 80 - 30 = 50 + 80 = 130
        if((($sum_total_pays - $old_amount) + $new_amount) > $request->appointment_total){
            return response()->json([
                "message" => 403,
                "message_text" => "EL MONTO QUE SE QUIERE EDITAR SUPERA AL COSTO DE LA CITA MEDICA",
            ]);
        }

        $appointment_pay->update([
            "amount" =>  $request->amount,
            "method_payment"=>  $request->method_payment,
        ]);

        $apppointment = Appointment::findOrFail($request->appointment_id);
        $is_total_payment = false;
        if(($apppointment->amount) == (($sum_total_pays - $old_amount) + $new_amount)){
            $apppointment->update(["status_pay" => 1]);
            $is_total_payment = true;
        }else{
            $apppointment->update(["status_pay" => 2]);
        }

        return response()->json([
            "message" => 200,
            "appointment_pay" => [
                "is_total_payment" => $is_total_payment,
                "id" => $appointment_pay->id,
                "appointment_id" => $appointment_pay->appointment_id,
                "amount" => $appointment_pay->amount,
                "method_payment" => $appointment_pay->method_payment,
                "created_at" => $appointment_pay->created_at->format("Y-m-d h:i A"),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $appointment_pay = AppointmentPay::findOrFail($id);
        $this->authorize('delete',$appointment_pay);
        $apppointment = Appointment::findOrFail($appointment_pay->appointment_id);
        $apppointment->update(["status_pay" => 2]);

        $appointment_pay->delete();

        return response()->json([
            "message" => 200,
        ]);
    }

    public function reporte(Request $request){

        ini_set('memory_limit', '512M');
        set_time_limit(500);

        $specialitie_id = $request->specialitie_id;
        $search_doctor = $request->search_doctor;
        $search_patient = $request->search_patient;
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $user = auth("api")->user();

        $appointments = Appointment::filterAdvancePay($specialitie_id,$search_doctor,$search_patient,$date_start,$date_end,$user)
        ->orderBy("status_pay","desc")
        ->get();
        $resultado = AppointmentPayCollection::make($appointments);
        $resultado = $resultado->toJson();
        $resultado = json_decode($resultado, true);
        // dd($resultado);
        // Generar el PDF
        $pdf = PDF::setPaper('letter','landscape')->loadView('ReportePays.pdf', compact('resultado'));
        // Retornar el PDF como respuesta
        return $pdf->download('reporte_citas.pdf');
    }

    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function recibo(Request $request){

            $data = $request->all();
            // Decodificar los parámetros JSON
            foreach ($data as $key => $value) {
                if ($this->isJson($value)) {
                    $data[$key] = json_decode($value, true);
                }
            }

            // Lógica para generar el PDF con los datos recibidos
            $pdf = PDF::loadView('ReportePays.recibo', $data);

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="recibo.pdf"');
            //$data = json_decode($data, true);
            // Generar el PDF

             $pdf = PDF::setPaper('letter','landscape')->loadView('ReportePays.recibo', compact('data'));
            // Retornar el PDF como respuesta
            return $pdf->download('reporte_citas.pdf');
    }
}

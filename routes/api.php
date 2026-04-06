<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\Rol\RolesController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Admin\Staff\StaffsController;
use App\Http\Controllers\Admin\Doctor\DoctorsController;
use App\Http\Controllers\Dashboard\DashboardKpiController;
use App\Http\Controllers\Admin\Doctor\SpecialityController;
use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Appointment\AppointmentPayController;
use App\Http\Controllers\Appointment\AppointmentAttentionController;
use App\Http\Controllers\Ingreso\IngresoController;
use App\Http\Controllers\Egreso\EgresoController;
use App\Http\Controllers\Seguimiento\SeguimientoController;
use App\Http\Controllers\Patient\PatientPortalController;
use App\Http\Controllers\Patient\PatientPaymentMethodController;
use App\Http\Controllers\Payment\StripeController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Booking\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ── LANDING PAGE (público) ───────────────────────────────────────────────
Route::prefix('public')->group(function () {
    Route::get('doctors',      [PatientPortalController::class, 'publicDoctors']);
    Route::get('specialities', [PatientPortalController::class, 'publicSpecialities']);
});

// ── REGISTRO PACIENTE (público) ──────────────────────────────────────────
Route::post('patient/register', [PatientPortalController::class, 'register']);

// ── STRIPE WEBHOOK (público, sin auth — Stripe llama aquí) ───────────────
Route::post('stripe/webhook', [StripeController::class, 'webhook']);

// ── BOOKING CONFIG (público) ─────────────────────────────────────────────
Route::get('booking/config',               [BookingController::class, 'config']);
Route::get('booking/doctor/{id}/schedule', [BookingController::class, 'doctorSchedule']);

Route::group([

    // 'middleware' => 'auth:api',
    'prefix' => 'auth',
    // 'middleware'=> ['role:admin','permission:publish articles'],

], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/list', [AuthController::class, 'list']);
    Route::post('/reg', [AuthController::class, 'reg']);
});

Route::group([

    'middleware' => 'auth:api',
    //'prefix' => 'auth',
    // 'middleware'=> ['role:admin','permission:publish articles'],

], function ($router) {
    Route::get('roles/reporte',[ RolesController::class,"reporte"]);
    Route::resource('roles', RolesController::class);
    Route::get('staffs/config', [StaffsController::class,"config"]);
    Route::get('staffs/reporte', [StaffsController::class,"reporte"]);
    Route::post('staffs/{id}', [StaffsController::class,"update"]);
    Route::resource('staffs', StaffsController::class);

    Route::get('specialities/reporte', [SpecialityController::class,"reporte"]);
    Route::resource("specialities",SpecialityController::class);

    Route::get("doctors/profile/{id}",[DoctorsController::class,"profile"]);
    Route::get('doctors/config', [DoctorsController::class,"config"]);
    Route::get('doctors/reporte', [DoctorsController::class,"reporte"]);
    Route::post('doctors/{id}', [DoctorsController::class,"update"]);
    Route::resource('doctors', DoctorsController::class);

    Route::get("patients/profile/{id}",[PatientController::class,"profile"]);
    Route::post('patients/{id}', [PatientController::class,"update"]);
    Route::get('patients/reporte', [PatientController::class,"reporte"]);
    Route::resource('patients', PatientController::class);

    Route::get("appointmet/config",[AppointmentController::class,"config"]);
    Route::get("appointmet/patient",[AppointmentController::class,"query_patient"]);
    //         /appointmet/reporte
    Route::get('appointmet/reporte', [AppointmentController::class,"reporte"]);
    Route::post("appointmet/filter",[AppointmentController::class,"filter"]);
    Route::post("appointmet/calendar",[AppointmentController::class,"calendar"]);
    Route::resource("appointmet",AppointmentController::class);

    Route::get("appointmet-pay/reporte",[AppointmentPayController::class,"reporte"]);
    Route::get("appointmet-pay/recibo",[AppointmentPayController::class,"recibo"]);
    Route::resource("appointmet-pay",AppointmentPayController::class);

    Route::get('/appointmet-attention/receta', [AppointmentAttentionController::class, 'receta']);
    Route::resource("appointmet-attention",AppointmentAttentionController::class);

    // =================== INGRESO ===================
    Route::get('ingresos/config',  [IngresoController::class, 'config']);
    Route::get('ingresos/reporte', [IngresoController::class, 'reporte']);
    Route::resource('ingresos', IngresoController::class);

    // =================== EGRESO ====================
    Route::get('egresos/config',   [EgresoController::class, 'config']);
    Route::get('egresos/reporte',  [EgresoController::class, 'reporte']);
    Route::resource('egresos', EgresoController::class);

    // ================== SEGUIMIENTO ================
    Route::get('seguimientos/config',  [SeguimientoController::class, 'config']);
    Route::get('seguimientos/reporte', [SeguimientoController::class, 'reporte']);
    Route::resource('seguimientos', SeguimientoController::class);

    // ── PORTAL DEL PACIENTE (auth:api + rol PACIENTE) ───────────────────
    Route::prefix('patient-portal')->group(function () {
        Route::get('my-profile',      [PatientPortalController::class, 'myProfile']);
        Route::get('my-appointments', [PatientPortalController::class, 'myAppointments']);
        Route::get('my-history',      [PatientPortalController::class, 'myHistory']);
        Route::get('my-seguimientos', [PatientPortalController::class, 'mySeguimientos']);
        // Pago de cita (Stripe o Billing) — requiere paciente autenticado
        Route::post('checkout',       [PaymentController::class, 'createSession']);

        // Métodos de pago del paciente
        Route::get('payment-methods',           [PatientPaymentMethodController::class, 'index']);
        Route::post('payment-methods',          [PatientPaymentMethodController::class, 'store']);
        Route::patch('payment-methods/{id}/default', [PatientPaymentMethodController::class, 'setDefault']);
        Route::delete('payment-methods/{id}',   [PatientPaymentMethodController::class, 'destroy']);
    });

    Route::post("dashboard/admin",[DashboardKpiController::class,"dashboard_admin"]);
    Route::post("dashboard/admin-year",[DashboardKpiController::class,"dashboard_admin_year"]);

    Route::post("dashboard/doctor",[DashboardKpiController::class,"dashboard_doctor"]);
    Route::get("dashboard/config",[DashboardKpiController::class,"config"]);
    Route::post("dashboard/doctor-year",[DashboardKpiController::class,"dashboard_doctor_year"]);

});


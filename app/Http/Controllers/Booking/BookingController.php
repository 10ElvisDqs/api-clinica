<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Doctor\DoctorScheduleDay;
use App\Models\Doctor\Specialitie;
use App\Models\User;

/**
 * Single Responsibility: solo expone la configuración pública de reservas.
 */
class BookingController extends Controller
{
    public function config()
    {
        $specialities = Specialitie::where('state', 1)
            ->select('id', 'name', 'price')
            ->orderBy('name')
            ->get();

        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'like', '%DOCTOR%'))
            ->whereHas('schedule_days.schedules_hours')
            ->select('id', 'name', 'surname')
            ->with(['specialitie:id,name'])
            ->get()
            ->map(fn($d) => [
                'id'          => $d->id,
                'full_name'   => 'Dr. ' . $d->name . ' ' . $d->surname,
                'avatar'      => null,
                'specialitie' => $d->specialitie?->name,
            ]);

        return response()->json([
            'specialities' => $specialities,
            'doctors'      => $doctors,
        ]);
    }

    public function doctorSchedule(int $doctorId)
    {
        $days = DoctorScheduleDay::where('user_id', $doctorId)
            ->with(['schedules_hours.doctor_schedule_hour'])
            ->whereNull('deleted_at')
            ->get()
            ->map(fn($day) => [
                'id'    => $day->id,
                'day'   => $day->day,
                'hours' => $day->schedules_hours->map(fn($jh) => [
                    'id'         => $jh->id,
                    'hour_start' => $jh->doctor_schedule_hour?->hour_start,
                    'hour_end'   => $jh->doctor_schedule_hour?->hour_end,
                    'label'      => $jh->doctor_schedule_hour
                        ? $jh->doctor_schedule_hour->hour_start . ' - ' . $jh->doctor_schedule_hour->hour_end
                        : null,
                ]),
            ]);

        return response()->json(['schedule' => $days]);
    }
}

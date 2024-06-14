<?php

namespace App\Http\Resources\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $HOUR_SCHEDULES = collect([]);
        $days_week = [];
        $days_week["Lunes"] = "table-primary";
        $days_week["Martes"] = "table-secondary";
        $days_week["Miercoles"] = "table-success";
        $days_week["Jueves"] = "table-warning";
        $days_week["Viernes"] = "table-info";
        $days_name = "";
        foreach ($this->resource->schedule_days as $key => $schedule_day) {
            $days_name .= ($schedule_day->day."-");
            foreach ($schedule_day->schedules_hours as $schedules_hour) {
                $HOUR_SCHEDULES->push([
                    "day"=>[
                        "day" => $schedule_day->day,
                        "class" => $days_week[$schedule_day->day],
                    ],
                    "day_name"=>$schedule_day->day,
                    "hours_day"=>[
                                    "hour"=> $schedules_hour->doctor_schedule_hour->hour,
                                    "format_hour"=> Carbon::parse( date("Y-m-d").' '.$schedules_hour->doctor_schedule_hour->hour.":00:00")->format("h:i A"),
                                    "items"=>[],
                                ],
                    "hour"=>$schedules_hour->doctor_schedule_hour->hour,
                    "grupo"=>"all",
                    "item"=>[
                                "id"=> $schedules_hour->doctor_schedule_hour->id ,
                                "hour_start"=> $schedules_hour->doctor_schedule_hour->hour_start,
                                "hour_end"=> $schedules_hour->doctor_schedule_hour->hour_end ,
                                "format_hour_start"=> Carbon::parse( date("Y-m-d").' '.$schedules_hour->doctor_schedule_hour->hour_start)->format("h:i A"),
                                "format_hour_end"=> Carbon::parse( date("Y-m-d").' '.$schedules_hour->doctor_schedule_hour->hour_end   )->format("h:i A"),
                                "hour"=> $schedules_hour->doctor_schedule_hour->hour ,
                        ],
                ]);
            }
        }
        return [
            'id'=>$this->resource->id,
            'name'=>$this->resource->name,
            'surname'=>$this->resource->surname,
            'email'=>$this->resource->email,
            'mobile'=>$this->resource->mobile,
            'birth_date'=>$this->resource->birth_date ? Carbon::parse($this->resource->birth_date)->format('Y/m/d') : NULL ,
            'gender'=>$this->resource->gender,
            'education'=>$this->resource->education,
            'designation'=>$this->resource->designation,
            'address'=>$this->resource->address,
            'created_at'=>$this->resource->created_at->format('Y/m/d'),
            'role'=>$this->resource->roles->first(),
            "specialitie_id" => $this->resource->specialitie_id,
            "specialitie" => $this->resource->specialitie ? [
                "id"=> $this->resource->specialitie->id,
                "name" => $this->resource->specialitie->name
                ]: "",
            'avatar'=> env('APP_URL').'storage/'.$this->resource->avatar,
            "schedule_selecteds"=> $HOUR_SCHEDULES,
            "days_name" => $days_name,
        ];
    }
}

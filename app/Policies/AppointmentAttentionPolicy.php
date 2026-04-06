<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Auth\Access\Response;
use App\Models\Appointment\AppointmentAttention;

class AppointmentAttentionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    // public function viewAny(User $user): bool
    // {

    // }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AppointmentAttention $appointmentAttention): bool
    {//

        if($user->can('attention_appointment')){//
            if(str_contains(Str::upper($user->roles->first()->name),'DOCTOR')){
                // DOCTOR
                if($user->id == $appointmentAttention->appointment->doctor_id){
                    return true;
                }
            }else{
                // EL USUARIO QUE HA REGISTRADO LA CITA MEDICA
                if($user->id == $appointmentAttention->appointment->user_id){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    // public function create(User $user): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can update the model.
     */
    // public function update(User $user, AppointmentAttention $appointmentAttention): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can delete the model.
     */
    // public function delete(User $user, AppointmentAttention $appointmentAttention): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, AppointmentAttention $appointmentAttention): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, AppointmentAttention $appointmentAttention): bool
    // {
    //     //
    // }
}

<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment\Appointment;
use Illuminate\Support\Str;
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if($user->can('list_appointment')){
            return true;
        }
        return false;
    }

    public function filter(User $user): bool
    {
        if($user->can('register_appointment') || $user->can('edit_appointment')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        // dd("hola");
        if($user->can('edit_appointment')){
            if(str_contains(Str::upper($user->roles->first()->name),'DOCTOR')){
                // DOCTOR
                if($user->id == $appointment->doctor_id){
                    return true;
                }
            }else{
                // EL USUARIO QUE HA REGISTRADO LA CITA MEDICA
                if($user->id == $appointment->user_id){
                    return true;
                }
            }
        }
        return false;
    }

    public function addPayment(User $user, Appointment $appointment): bool
    {
        if($user->can('edit_appointment')){
            // EL USUARIO QUE HA REGISTRADO LA CITA MEDICA
            if($user->id == $appointment->user_id){
                return true;
            }
        }
        return false;
    }

    public function viewAppointment(User $user, Appointment $appointment): bool
    {
        if($user->can('attention_appointment')){
            if(str_contains(Str::upper($user->roles->first()->name),'DOCTOR')){
                // DOCTOR
                if($user->id == $appointment->doctor_id){
                    return true;
                }
            }else{
                // EL USUARIO QUE HA REGISTRADO LA CITA MEDICA
                if($user->id == $appointment->user_id){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if($user->can('register_appointment')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if($user->can('edit_appointment')){
            if(str_contains(Str::upper($user->roles->first()->name),'DOCTOR')){
                // DOCTOR
                if($user->id == $appointment->doctor_id){
                    return true;
                }
            }else{
                // EL USUARIO QUE HA REGISTRADO LA CITA MEDICA
                if($user->id == $appointment->user_id){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        if($user->can('delete_appointment')){
            return true;
        }
        if(str_contains(Str::upper($user->roles->first()->name),'DOCTOR')){
            // DOCTOR
            if($user->id == $appointment->doctor_id){
                return true;
            }
        }else{
            // EL USUARIO QUE HA REGISTRADO LA CITA MEDICA
            if($user->id == $appointment->user_id){
                return true;
            }
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, Appointment $appointment): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, Appointment $appointment): bool
    // {
    //     //
    // }
}

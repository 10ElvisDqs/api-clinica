<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->timestamp('date_appointment')->nullable();
            $table->unsignedBigInteger('specialitie_id')->nullable();
            $table->unsignedBigInteger('doctor_schedule_join_hour_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->double('amount')->nullable()->comment('costo total de la cita medica');
            $table->tinyInteger('status_pay')->default(1)->comment('1 pendiente, 2 pagado');
            $table->tinyInteger('status')->default(1)->comment('1 pendiente, 2 atendido');
            $table->timestamp('date_attention')->nullable();
            $table->tinyInteger('cron_state')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

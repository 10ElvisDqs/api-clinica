<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('user_id')->comment('quien registra');
            $table->unsignedBigInteger('appointment_attention_id')->nullable()->comment('atencion de referencia');
            $table->date('fecha_seguimiento');
            $table->text('motivo');
            $table->text('observaciones')->nullable();
            $table->tinyInteger('estado')->default(1)->comment('1=pendiente, 2=realizado, 3=cancelado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};

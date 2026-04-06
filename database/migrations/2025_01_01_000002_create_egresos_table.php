<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egresos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ingreso_id')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('user_id')->comment('quien registra');
            $table->dateTime('fecha_egreso');
            $table->string('tipo_egreso', 50)->comment('alta_medica, referido, voluntario, fallecido');
            $table->text('diagnostico_final');
            $table->text('indicaciones')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};

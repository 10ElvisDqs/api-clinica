<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('user_id')->comment('quien registra');
            $table->dateTime('fecha_ingreso');
            $table->text('motivo');
            $table->string('sala', 100)->nullable();
            $table->string('cama', 50)->nullable();
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 2=con_egreso');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingresos');
    }
};

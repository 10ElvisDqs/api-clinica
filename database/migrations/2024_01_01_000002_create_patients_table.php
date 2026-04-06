<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 250)->nullable();
            $table->string('surname', 250)->nullable();
            $table->string('email', 250)->nullable();
            $table->string('mobile', 25)->nullable();
            $table->string('n_document', 50)->nullable();
            $table->timestamp('birth_date')->nullable();
            $table->text('antecedent_family')->nullable();
            $table->text('antecedent_personal')->nullable();
            $table->text('antecedent_allergic')->nullable();
            $table->string('ta', 250)->nullable()->comment('presion arterial');
            $table->string('temperatura', 20)->nullable();
            $table->string('fc', 50)->nullable()->comment('frecuencia cardiaca');
            $table->string('fr', 50)->nullable()->comment('frecuencia respiratoria');
            $table->string('peso', 25)->nullable();
            $table->text('current_disease')->nullable()->comment('enfermedad actual');
            $table->tinyInteger('gender')->default(1)->comment('1 masculino, 2 femenino');
            $table->string('education', 250)->nullable();
            $table->string('avatar', 250)->nullable();
            $table->string('address', 250)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};

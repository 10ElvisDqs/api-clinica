<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_persons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('name_companion', 250)->nullable();
            $table->string('surname_companion', 250)->nullable();
            $table->string('mobile_companion', 250)->nullable();
            $table->string('relationship_companion', 250)->nullable();
            $table->string('name_responsible', 250)->nullable();
            $table->string('surname_responsible', 250)->nullable();
            $table->string('mobile_responsible', 250)->nullable();
            $table->string('relationship_responsible', 250)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_persons');
    }
};

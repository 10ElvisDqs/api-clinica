<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_schedule_hours', function (Blueprint $table) {
            $table->id();
            $table->string('hour_start', 50);
            $table->string('hour_end', 50);
            $table->string('hour', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_schedule_hours');
    }
};

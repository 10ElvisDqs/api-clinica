<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_schedule_join_hours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('doctor_schedule_day_id')->nullable();
            $table->unsignedBigInteger('doctor_schedule_hour_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('doctor_schedule_day_id')
                  ->references('id')->on('doctor_schedule_days')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_schedule_join_hours');
    }
};

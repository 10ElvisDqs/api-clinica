<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->enum('type', ['bank_transfer', 'tigo_money', 'cash', 'qr'])
                  ->default('cash');
            $table->string('label');                    // "Cuenta BNB Personal"
            $table->string('bank_name')->nullable();    // "BNB", "Banco Unión", "BCP"
            $table->string('account_number')->nullable(); // número de cuenta
            $table->string('account_holder')->nullable(); // titular de la cuenta
            $table->string('phone_number')->nullable(); // para Tigo Money / QR
            $table->boolean('is_default')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_payment_methods');
    }
};

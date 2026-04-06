<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('specialities', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->default(100.00)->after('name')
                  ->comment('Precio de la consulta en soles');
        });
    }

    public function down(): void
    {
        Schema::table('specialities', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};

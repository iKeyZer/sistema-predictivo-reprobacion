<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->tinyInteger('semester');
            $table->tinyInteger('credits')->default(5);
            $table->decimal('historical_difficulty', 5, 2)->default(0.00)
                  ->comment('Porcentaje histórico de reprobación 0-100');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};

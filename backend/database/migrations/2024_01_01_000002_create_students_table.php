<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('control_number', 20)->unique();
            $table->string('career', 100)->default('Ingeniería en Sistemas Computacionales');
            $table->tinyInteger('semester')->default(1);
            $table->year('enrollment_year');
            $table->enum('status', ['activo', 'baja', 'egresado'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

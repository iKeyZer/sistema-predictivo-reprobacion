<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->enum('risk_level', ['bajo', 'medio', 'alto']);
            $table->decimal('risk_probability', 5, 4)->comment('0.0000 a 1.0000');
            $table->decimal('avg_grade', 5, 2)->comment('Promedio snapshot');
            $table->decimal('attendance_pct', 5, 2)->comment('% asistencia snapshot');
            $table->tinyInteger('failed_subjects')->default(0);
            $table->tinyInteger('academic_load')->default(0)->comment('Materias cursando');
            $table->string('model_version', 20)->default('heuristic');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_predictions');
    }
};

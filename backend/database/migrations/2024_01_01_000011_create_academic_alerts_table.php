<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_id')->constrained('risk_predictions')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['riesgo_alto', 'riesgo_medio', 'asistencia', 'calificacion']);
            $table->text('message');
            $table->enum('status', ['activa', 'atendida', 'descartada'])->default('activa');
            $table->json('notified_to')->nullable()->comment('Array de user_ids notificados');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_alerts');
    }
};

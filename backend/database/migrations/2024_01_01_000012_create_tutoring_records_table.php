<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutoring_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('tutor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('alert_id')->nullable()->constrained('academic_alerts')->onDelete('set null');
            $table->date('session_date');
            $table->enum('type', ['tutoria', 'asesoria', 'seguimiento'])->default('tutoria');
            $table->text('notes');
            $table->string('outcome', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutoring_records');
    }
};

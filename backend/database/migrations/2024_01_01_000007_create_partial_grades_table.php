<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partial_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('partial_number')->comment('1, 2 o 3');
            $table->decimal('grade', 5, 2);
            $table->decimal('activities_grade', 5, 2)->nullable();
            $table->decimal('participation_grade', 5, 2)->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->unique(['enrollment_id', 'partial_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partial_grades');
    }
};

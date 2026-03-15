<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontogram_teeth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('tooth_number', 10);
            $table->enum('status', ['healthy', 'caries', 'filling', 'root_canal', 'crown', 'implant', 'extracted', 'bridge', 'under_treatment', 'needs_treatment'])->default('healthy');
            $table->string('surface')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->timestamps();

            $table->unique(['patient_id', 'tooth_number']);
            $table->index('status');
            $table->index('visit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontogram_teeth');
    }
};


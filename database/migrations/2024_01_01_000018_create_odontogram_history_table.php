<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontogram_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('tooth_number', 10);
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('surface')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('patient_id');
            $table->index('tooth_number');
            $table->index('visit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontogram_history');
    }
};


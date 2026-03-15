<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_tickets', function (Blueprint $table) {
            $table->id();
            $table->date('ticket_date');
            $table->unsignedInteger('ticket_number');
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('visit_id')->nullable();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['waiting', 'called', 'with_doctor', 'done', 'missed', 'cancelled'])->default('waiting');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['ticket_date', 'ticket_number']);
            $table->index('appointment_id');
            $table->index('visit_id');
            $table->index('patient_id');
            $table->index(['ticket_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_tickets');
    }
};


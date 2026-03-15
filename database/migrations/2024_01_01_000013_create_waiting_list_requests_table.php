<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiting_list_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->date('preferred_date')->nullable();
            $table->time('preferred_from_time')->nullable();
            $table->time('preferred_to_time')->nullable();
            $table->enum('status', ['waiting', 'notified', 'expired', 'booked', 'cancelled'])->default('waiting');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('booked_appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_id', 'status']);
            $table->index(['patient_id', 'status']);
            $table->index('preferred_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiting_list_requests');
    }
};


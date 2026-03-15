<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('tooth_number', 10)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('session_no')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('planned_date')->nullable();
            $table->foreignId('completed_visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->timestamps();

            $table->index('treatment_plan_id');
            $table->index('service_id');
            $table->index('status');
            $table->index('completed_visit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plan_items');
    }
};


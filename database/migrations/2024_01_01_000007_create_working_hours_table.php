<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('working_day_id')->constrained()->cascadeOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('max_patients_per_day')->nullable();
            $table->unsignedSmallInteger('slot_granularity_minutes')->default(15);
            $table->timestamps();

            $table->index('working_day_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};


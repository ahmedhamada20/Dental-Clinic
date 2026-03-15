<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('specialty_id')
                ->nullable()
                ->after('assigned_doctor_id')
                ->constrained('medical_specialties')
                ->restrictOnDelete();

            $table->index('specialty_id');
        });

        DB::table('appointments')
            ->select('appointments.id', 'service_categories.medical_specialty_id')
            ->join('services', 'services.id', '=', 'appointments.service_id')
            ->join('service_categories', 'service_categories.id', '=', 'services.category_id')
            ->whereNull('appointments.specialty_id')
            ->orderBy('appointments.id')
            ->get()
            ->each(function ($appointment) {
                DB::table('appointments')
                    ->where('id', $appointment->id)
                    ->update(['specialty_id' => $appointment->medical_specialty_id]);
            });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->dropIndex(['specialty_id']);
            $table->dropColumn('specialty_id');
        });
    }
};

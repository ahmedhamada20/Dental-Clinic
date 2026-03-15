<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visit_notes', function (Blueprint $table) {
            // Add generic medical fields
            $table->foreignId('doctor_id')->nullable()->after('visit_id')->constrained('users')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->after('doctor_id')->constrained('patients')->nullOnDelete();
            $table->text('diagnosis')->nullable()->after('patient_id');
            $table->text('treatment_plan')->nullable()->after('diagnosis');
            $table->date('follow_up_date')->nullable()->after('treatment_plan');
            $table->json('attachments')->nullable()->after('follow_up_date');

            // Rename note to notes for semantic clarity
            // We keep `note` column but add an alias via the model; no rename to avoid data loss

            // Drop dental-specific columns
            $table->dropIndex(['tooth_number']);
            $table->dropColumn('tooth_number');

            // Drop the old note_type enum column
            $table->dropIndex(['note_type']);
            $table->dropColumn('note_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visit_notes', function (Blueprint $table) {
            // Restore dental-specific columns
            $table->enum('note_type', ['complaint', 'diagnosis', 'clinical', 'follow_up', 'internal'])
                  ->default('clinical')
                  ->after('visit_id');
            $table->string('tooth_number', 10)->nullable()->after('note_type');
            $table->index('note_type');
            $table->index('tooth_number');

            // Drop generic fields
            $table->dropConstrainedForeignId('patient_id');
            $table->dropConstrainedForeignId('doctor_id');
            $table->dropColumn(['diagnosis', 'treatment_plan', 'follow_up_date', 'attachments']);
        });
    }
};

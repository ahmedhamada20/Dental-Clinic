<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visit_notes', function (Blueprint $table) {
            $table->string('tooth_number', 10)->nullable()->after('note_type');
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();

            $table->index('tooth_number');
        });
    }

    public function down(): void
    {
        Schema::table('visit_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropIndex(['tooth_number']);
            $table->dropColumn('tooth_number');
        });
    }
};


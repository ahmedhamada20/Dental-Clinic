<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            if (!Schema::hasColumn('prescription_items', 'dose_duration')) {
                $table->string('dose_duration')->nullable()->after('frequency');
            }

            if (!Schema::hasColumn('prescription_items', 'treatment_duration')) {
                $table->string('treatment_duration')->nullable()->after('dose_duration');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            if (Schema::hasColumn('prescription_items', 'treatment_duration')) {
                $table->dropColumn('treatment_duration');
            }

            if (Schema::hasColumn('prescription_items', 'dose_duration')) {
                $table->dropColumn('dose_duration');
            }
        });
    }
};


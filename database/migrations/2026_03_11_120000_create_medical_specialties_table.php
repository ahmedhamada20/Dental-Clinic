<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_specialties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });

        if (Schema::hasTable('service_categories') && Schema::hasColumn('service_categories', 'medical_specialty_id')) {
            $defaultSpecialtyId = DB::table('medical_specialties')
                ->orderBy('id')
                ->value('id');

            if ($defaultSpecialtyId !== null) {
                DB::table('service_categories')
                    ->whereNull('medical_specialty_id')
                    ->update(['medical_specialty_id' => $defaultSpecialtyId]);
            }

            Schema::table('service_categories', function (Blueprint $table) {
                $table->foreign('medical_specialty_id')
                    ->references('id')
                    ->on('medical_specialties')
                    ->cascadeOnDelete();

                $table->unique(['medical_specialty_id', 'name_en'], 'service_categories_specialty_name_en_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('service_categories') && Schema::hasColumn('service_categories', 'medical_specialty_id')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropUnique('service_categories_specialty_name_en_unique');
                $table->dropForeign('service_categories_medical_specialty_id_foreign');
            });
        }

        Schema::dropIfExists('medical_specialties');
    }
};

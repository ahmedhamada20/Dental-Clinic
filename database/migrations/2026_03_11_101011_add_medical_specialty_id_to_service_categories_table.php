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
        Schema::table('service_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('service_categories', 'medical_specialty_id')) {
                $table->unsignedBigInteger('medical_specialty_id')->nullable()->after('id');
                $table->index('medical_specialty_id');
            }
        });

        if (! Schema::hasTable('medical_specialties')) {
            return;
        }

        $defaultSpecialtyId = DB::table('medical_specialties')
            ->orderBy('id')
            ->value('id');

        if ($defaultSpecialtyId !== null) {
            DB::table('service_categories')
                ->whereNull('medical_specialty_id')
                ->update(['medical_specialty_id' => $defaultSpecialtyId]);
        }

        Schema::table('service_categories', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $foreignKeys = collect($sm->listTableForeignKeys('service_categories'))->pluck('name');
            $indexes = collect($sm->listTableIndexes('service_categories'))->keys();

            if (! $foreignKeys->contains('service_categories_medical_specialty_id_foreign')) {
                $table->foreign('medical_specialty_id')
                    ->references('id')
                    ->on('medical_specialties')
                    ->cascadeOnDelete();
            }

            if (! $indexes->contains('service_categories_specialty_name_en_unique')) {
                $table->unique(['medical_specialty_id', 'name_en'], 'service_categories_specialty_name_en_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            if (Schema::hasColumn('service_categories', 'medical_specialty_id')) {
                try {
                    $table->dropUnique('service_categories_specialty_name_en_unique');
                } catch (Throwable $e) {
                    // Ignore if the unique index was never created.
                }

                try {
                    $table->dropForeign('service_categories_medical_specialty_id_foreign');
                } catch (Throwable $e) {
                    // Ignore if the foreign key was never created.
                }

                $table->dropIndex(['medical_specialty_id']);
                $table->dropColumn('medical_specialty_id');
            }
        });
    }
};

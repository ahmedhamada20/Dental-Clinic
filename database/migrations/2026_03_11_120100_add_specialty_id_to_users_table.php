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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('specialty_id')->nullable()->after('user_type');
            $table->index('specialty_id', 'users_specialty_id_index');
            $table->foreign('specialty_id', 'users_specialty_id_foreign')
                ->references('id')
                ->on('medical_specialties')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_specialty_id_foreign');
            $table->dropIndex('users_specialty_id_index');
            $table->dropColumn('specialty_id');
        });
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visit_tickets', function (Blueprint $table) {
            $table->foreign('visit_id')->references('id')->on('visits')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visit_tickets', function (Blueprint $table) {
            $table->dropForeign(['visit_id']);
        });
    }
};


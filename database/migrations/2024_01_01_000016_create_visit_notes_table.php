<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->enum('note_type', ['complaint', 'diagnosis', 'clinical', 'follow_up', 'internal'])->default('clinical');
            $table->longText('note');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('visit_id');
            $table->index('note_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_notes');
    }
};


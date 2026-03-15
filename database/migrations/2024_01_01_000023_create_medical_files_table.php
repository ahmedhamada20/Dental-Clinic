<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('file_category', ['xray', 'prescription', 'treatment_document', 'before_after', 'lab_result', 'other'])->default('other');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_extension', 20)->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->boolean('is_visible_to_patient')->default(true);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index('patient_id');
            $table->index('visit_id');
            $table->index('file_category');
            $table->index('is_visible_to_patient');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_files');
    }
};


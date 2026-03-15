<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('treatment_plan_item_id')->nullable()->constrained('treatment_plan_items')->nullOnDelete();
            $table->enum('item_type', ['service', 'manual', 'treatment_session'])->default('service');
            $table->string('item_name_ar');
            $table->string('item_name_en')->nullable();
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->nullable()->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('tooth_number', 10)->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('service_id');
            $table->index('treatment_plan_item_id');
            $table->index('item_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};


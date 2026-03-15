<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->enum('channel', ['push', 'in_app', 'system'])->default('push');
            $table->string('title');
            $table->text('body');
            $table->enum('type', [
                'appointment_created',
                'appointment_confirmed',
                'appointment_cancelled',
                'appointment_reminder',
                'waiting_slot_available',
                'invoice_created',
                'file_uploaded',
                'treatment_updated',
                'payment_received'
            ]);
            $table->json('data')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'read'])->default('pending');
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('type');
            $table->index('status');
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};


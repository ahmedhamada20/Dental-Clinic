<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('system_notification_id')->nullable()->index();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('channel');          // database, email, sms, push
            $table->string('notification_type'); // appointment_reminder, billing_due, etc.
            $table->string('title');
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();   // recipient email/phone, token count, etc.
            $table->timestamp('sent_at')->nullable();
            $table->unsignedBigInteger('triggered_by')->nullable(); // user id
            $table->string('triggered_by_type')->nullable();        // 'manual', 'scheduled', 'auto'
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('channel');
            $table->index('status');
            $table->index('notification_type');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};


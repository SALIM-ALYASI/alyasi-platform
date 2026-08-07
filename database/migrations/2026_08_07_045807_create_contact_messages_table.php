<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('phone', 30)->nullable();
            $table->string('subject', 255)->nullable();
            $table->text('message');

            /*
             * حالة الرسالة:
             * new      = جديدة
             * read     = تمت قراءتها
             * replied  = تم الرد عليها
             */
            $table->string('status', 30)->default('new');

            $table->string('ip_address', 45)->nullable();
            $table->string('device_token', 64)->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};

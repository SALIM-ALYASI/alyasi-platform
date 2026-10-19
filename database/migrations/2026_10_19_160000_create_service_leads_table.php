<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * طلبات خدمات منشورة علنًا (مطلوب مصمم/مونتير/موقع) يلقطها Grok من منصات
 * التواصل في عُمان والخليج. نحفظ النص العام ورابطه بس، وتنحذف بعد 60 يوم.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_leads', function (Blueprint $table): void {
            $table->id();
            $table->string('agent', 20)->default('grok');
            $table->string('platform', 20);
            $table->string('url', 500);
            $table->string('url_key', 64)->unique();
            $table->string('text_hash', 64)->index();
            $table->string('author', 120)->nullable();
            $table->text('text');
            $table->string('summary', 400)->nullable();
            $table->string('category', 20);
            $table->char('country', 2);
            $table->string('city', 80)->nullable();
            $table->string('budget', 120)->nullable();
            $table->string('deadline', 120)->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->string('status', 20)->default('new')->index();
            $table->string('reject_reason', 30)->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_leads');
    }
};

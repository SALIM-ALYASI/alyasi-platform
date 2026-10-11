<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تصنيف الزيارات (بشري مرجّح/مشتبه/بوت/سالم/فحص) بدون حذف أي صف قديم:
     * الصفوف السابقة تبقى traffic_class = null = «غير منقّاة».
     */
    public function up(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->string('traffic_class', 12)->nullable()->after('user_agent');
            $table->string('class_reasons')->nullable()->after('traffic_class');
            $table->string('visitor_id', 32)->nullable()->after('class_reasons');
            // عنوان الوسيط (Cloudflare) لما يكون غير عنوان الزائر -- للتدقيق.
            $table->string('proxy_ip', 45)->nullable()->after('ip_address');

            $table->index(['traffic_class', 'created_at']);
            $table->index('visitor_id');
        });

        Schema::create('page_events', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id', 32)->nullable();
            $table->string('path');
            // reached_end | audio_play | audio_complete
            $table->string('type', 20);
            $table->string('label')->nullable();
            $table->unsignedInteger('seconds')->nullable();
            $table->string('traffic_class', 12)->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index('visitor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_events');

        Schema::table('page_visits', function (Blueprint $table) {
            $table->dropIndex(['traffic_class', 'created_at']);
            $table->dropIndex(['visitor_id']);
            $table->dropColumn(['traffic_class', 'class_reasons', 'visitor_id', 'proxy_ip']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * أكثر من وكيل اكتشاف (Grok و Manus): وقت نشر الخبر الأصلي وتقدير الوكيل
 * لأهميته -- للمقارنة بين الوكلاء (الحداثة، ودقة تقديرهم مقابل رئيس التحرير).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_suggestions', function (Blueprint $table) {
            $table->timestamp('source_published_at')->nullable()->after('country');
            $table->string('importance', 20)->nullable()->after('source_published_at');
            $table->index('suggested_by');
        });
    }

    public function down(): void
    {
        Schema::table('news_suggestions', function (Blueprint $table) {
            $table->dropIndex(['suggested_by']);
            $table->dropColumn(['source_published_at', 'importance']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دمج الأخبار المكررة: الخبر المكرر ينحذف (soft delete) ويحفظ رقم الخبر
 * الأصلي، فروابطه القديمة تتحوّل 301 للأصلي بدل 410.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->unsignedBigInteger('merged_into_id')->nullable()->after('daily_sequence')->index();
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropIndex(['merged_into_id']);
            $table->dropColumn('merged_into_id');
        });
    }
};

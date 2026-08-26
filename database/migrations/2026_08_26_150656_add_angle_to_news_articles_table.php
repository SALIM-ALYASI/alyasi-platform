<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            // price_reality | what_changed | who_cares | what_broke -- الزاوية
            // اللي بُني عليها ترتيب الخبر بدل نسخ ترتيب المصدر (اختياري،
            // للشفافية الإدارية فقط -- لا يؤثر على منطق العرض).
            $table->string('angle', 32)->nullable()->after('analysis_regional_angle_ar');
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropColumn('angle');
        });
    }
};

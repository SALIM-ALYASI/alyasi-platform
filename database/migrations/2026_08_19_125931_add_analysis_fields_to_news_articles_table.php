<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->string('analysis_status', 20)
                ->default('none')
                ->after('content_en');

            $table->string('analysis_title_ar', 500)
                ->nullable()
                ->after('analysis_status');

            $table->longText('analysis_ar')
                ->nullable()
                ->after('analysis_title_ar');

            $table->longText('analysis_regional_angle_ar')
                ->nullable()
                ->after('analysis_ar');
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropColumn([
                'analysis_status',
                'analysis_title_ar',
                'analysis_ar',
                'analysis_regional_angle_ar',
            ]);
        });
    }
};

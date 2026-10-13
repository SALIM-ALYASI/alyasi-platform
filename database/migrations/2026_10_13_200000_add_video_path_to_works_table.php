<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فيديو اختياري للعمل (مثل الإعلانات): يظهر مشغّل بدل صورة الغلاف بصفحة التفاصيل.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->string('video_path', 500)->nullable()->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->dropColumn('video_path');
        });
    }
};

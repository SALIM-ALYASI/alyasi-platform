<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تقسيم صورة المقال إلى صورة عربية وصورة إنجليزية منفصلتين (بدل صورة
 * واحدة مشتركة). نستخدم إضافة عمود + نسخ البيانات + حذف العمود القديم
 * بدل renameColumn() لأن doctrine/dbal غير مثبّت بالمشروع.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('featured_image_ar')->nullable()->after('content_en');
            $table->string('featured_image_en')->nullable()->after('featured_image_ar');
        });

        DB::table('articles')->update([
            'featured_image_ar' => DB::raw('featured_image'),
        ]);

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('featured_image');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('featured_image')->nullable()->after('content_en');
        });

        DB::table('articles')->update([
            'featured_image' => DB::raw('featured_image_ar'),
        ]);

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['featured_image_ar', 'featured_image_en']);
        });
    }
};

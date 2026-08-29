<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_editions', function (Blueprint $table) {
            /*
             * صور التغطية الميدانية (local_field) - شرائح، منتجات، لحظات
             * فعلية من الحدث. مختلفة عن image (صورة الغلاف og:image الواحدة)
             * -- هذي مجموعة صور تُعرض بجسم التقرير بعد الحضور الفعلي.
             */
            $table->json('gallery')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('event_editions', function (Blueprint $table) {
            $table->dropColumn('gallery');
        });
    }
};

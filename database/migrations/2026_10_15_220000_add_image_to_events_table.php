<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * صورة المؤتمر نفسه (مثل شعار الياسي مع شعار الجهة المنظمة) لبطاقته بقائمة
 * المؤتمرات -- غير صورة كل سنة (البوستر) اللي تظهر ببطاقة السنة وصفحتها.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('image', 500)->nullable()->after('organizer');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};

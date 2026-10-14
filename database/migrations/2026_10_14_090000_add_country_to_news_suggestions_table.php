<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دولة الخبر من الوكيل (رمز ISO مثل OM) -- تمهيد لصفحة أخبار عُمان، ولقياس
 * كم خبر محلي يجينا فعلًا قبل ما نبنيها.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_suggestions', function (Blueprint $table) {
            $table->char('country', 2)->nullable()->after('source_name')->index();
        });
    }

    public function down(): void
    {
        Schema::table('news_suggestions', function (Blueprint $table) {
            $table->dropIndex(['country']);
            $table->dropColumn('country');
        });
    }
};

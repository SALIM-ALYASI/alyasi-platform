<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تأكيد الزائر البشري: الصفحة ترسل إشارة «view» بعد ثانيتين ظاهرة بمتصفح يشغّل
     * جافاسكربت ويحفظ الكوكي. روبوتات تتنكّر بهوية كروم (كانت تنحسب «بشري مرجّح»
     * من الصين والبرازيل وغيرها) ما ترسلها، فما تنحسب من analytics.confirm_since.
     */
    public function up(): void
    {
        Schema::table('page_visits', function (Blueprint $table): void {
            $table->timestamp('confirmed_at')->nullable()->after('visitor_id');
        });
    }

    public function down(): void
    {
        Schema::table('page_visits', function (Blueprint $table): void {
            $table->dropColumn('confirmed_at');
        });
    }
};

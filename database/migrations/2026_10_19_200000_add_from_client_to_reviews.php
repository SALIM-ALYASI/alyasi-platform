<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تقييم وصل من رابط طلب التقييم الموقّع (العميل نفسه) -- يظهر بشارة «عميل المشروع».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->boolean('from_client')->default(false)->after('status');
        });

        // تقييم الحرفيون (19 أكتوبر 2026) وصل من رابط العميل قبل هالعمود: نعلّمه، ونعتمده بطلب سالم.
        DB::table('reviews')->where('id', 4)->where('reviewable_type', 'work')->where('name', 'مطابق')
            ->update(['from_client' => true, 'status' => 'approved', 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropColumn('from_client');
        });
    }
};

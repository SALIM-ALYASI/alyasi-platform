<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * نسخة إنجليزية اختيارية لنصوص العمل + حالة المشروع (قيد التطوير).
 * كلها nullable: الأعمال القديمة ما تتأثر وتبقى تعرض نصها العربي في الصفحتين.
 *
 * وكذلك نوع «application» (تطبيق): موجود بلوحة التحكم من البداية بس ناقص من
 * enum العمود، فكان حفظ عمل من نوع تطبيق يفشل.
 */
return new class extends Migration
{
    private const TYPES = ['website', 'design', 'advertisement', 'video', 'photography', 'event', 'personal', 'other'];

    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->enum('type', [...self::TYPES, 'application'])->default('other')->change();
        });

        Schema::table('works', function (Blueprint $table) {
            $table->string('title_en', 255)->nullable()->after('title');
            $table->string('short_description_en', 500)->nullable()->after('short_description');
            $table->string('outcome_en', 255)->nullable()->after('outcome');
            $table->text('description_en')->nullable()->after('description');
            $table->string('status', 30)->nullable()->after('completed_at');
            $table->boolean('cover_is_illustrative')->default(false)->after('cover_image');
        });
    }

    public function down(): void
    {
        DB::table('works')->where('type', 'application')->update(['type' => 'other']);

        Schema::table('works', function (Blueprint $table) {
            $table->enum('type', self::TYPES)->default('other')->change();
        });

        Schema::table('works', function (Blueprint $table) {
            $table->dropColumn([
                'title_en',
                'short_description_en',
                'outcome_en',
                'description_en',
                'status',
                'cover_is_illustrative',
            ]);
        });
    }
};

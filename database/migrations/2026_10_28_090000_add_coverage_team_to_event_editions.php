<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * فريق تغطية المؤتمر: «تابعوا المؤتمر مع تغطية …» + زر حساب لكل شخص له رابط.
     * يُدار من لوحة التحكم: شخص بكل سطر «الاسم بالعربي | English name | رابط الحساب».
     */
    public function up(): void
    {
        Schema::table('event_editions', function (Blueprint $table): void {
            $table->text('coverage_team')->nullable()->after('goals_en');
        });

        // CYSEC Oman 2026 (19 نوفمبر): تغطية سالم الحجري ومروة ماهر (طلب سالم، 28 أكتوبر).
        $id = DB::table('permalinks')
            ->where('linkable_type', 'event_edition')->where('locale', 'ar')->where('slug', 'cysec-oman-2026')
            ->value('linkable_id');

        if ($id) {
            DB::table('event_editions')->where('id', $id)->whereNull('coverage_team')->update([
                'coverage_team' => "سالم الحجري | Salem Al Hajri\nمروة ماهر | Marwa Maher | https://www.instagram.com/1almarwa/",
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('event_editions', function (Blueprint $table): void {
            $table->dropColumn('coverage_team');
        });
    }
};

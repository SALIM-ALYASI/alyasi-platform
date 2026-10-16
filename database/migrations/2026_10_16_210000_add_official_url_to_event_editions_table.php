<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * الموقع الرسمي للمؤتمر: رابط ظاهر بالصفحة، ومصدر نتحقق منه يوميًا من
     * الموعد (COMEX غيّر موعده أكثر من مرة).
     */
    public function up(): void
    {
        Schema::table('event_editions', function (Blueprint $table): void {
            $table->string('official_url', 1000)->nullable()->after('livestream_url');
            $table->timestamp('official_checked_at')->nullable()->after('official_url');
            $table->string('official_check_note', 500)->nullable()->after('official_checked_at');
        });

        DB::table('event_editions')
            ->where('year', 2027)
            ->whereIn('event_id', DB::table('events')->where('slug', 'comex-oman')->pluck('id'))
            ->update(['official_url' => 'https://comex-global.com/']);
    }

    public function down(): void
    {
        Schema::table('event_editions', function (Blueprint $table): void {
            $table->dropColumn(['official_url', 'official_checked_at', 'official_check_note']);
        });
    }
};

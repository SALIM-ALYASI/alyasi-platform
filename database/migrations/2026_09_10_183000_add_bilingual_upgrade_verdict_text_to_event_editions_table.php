<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_editions', function (Blueprint $table) {
            $table->text('upgrade_verdict_text_ar')->nullable()->after('upgrade_verdict_text');
            $table->text('upgrade_verdict_text_en')->nullable()->after('upgrade_verdict_text_ar');
        });

        // Preserve all existing verdict text as Arabic. The legacy column stays
        // untouched for rollback/backward compatibility.
        DB::table('event_editions')
            ->whereNotNull('upgrade_verdict_text')
            ->orderBy('id')
            ->get(['id', 'upgrade_verdict_text'])
            ->each(function ($edition) {
                DB::table('event_editions')
                    ->where('id', $edition->id)
                    ->update([
                        'upgrade_verdict_text_ar' => str_replace('**', '', (string) $edition->upgrade_verdict_text),
                    ]);
            });

        // Backfill the existing Apple 2026 English verdict so the English page
        // never falls back to Arabic after this migration.
        $appleEventId = DB::table('events')->where('slug', 'apple')->value('id');

        if ($appleEventId) {
            DB::table('event_editions')
                ->where('event_id', $appleEventId)
                ->where('year', 2026)
                ->whereNull('upgrade_verdict_text_en')
                ->update([
                    'upgrade_verdict_text_en' => <<<'TEXT'
My view on upgrading

If you're using iPhone 14 or iPhone 15, the new generation may be an upgrade worth considering, especially if the new features genuinely matter to you.

As for iPhone Duo at $1,999, I'd prefer to wait. It's the first generation in this category, so it's better to wait for real-world testing before buying and see how reliable it is and what issues may appear with use.

For AirPods 5, I think upgrading makes more sense for AirPods 3 users or anyone on an older model.

For Apple Watch, upgrading seems more suitable for Series 10 owners or older. Users with newer generations may be better off waiting unless the new features are important to them.

Bottom line: You don't need to upgrade just because a new generation exists; compare what you have with the features you'll actually use, then decide.
TEXT,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('event_editions', function (Blueprint $table) {
            $table->dropColumn([
                'upgrade_verdict_text_ar',
                'upgrade_verdict_text_en',
            ]);
        });
    }
};

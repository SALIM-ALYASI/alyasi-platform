<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * اسم العميل بالإنجليزية: صفحات الأعمال الإنجليزية كانت تعرض الاسم العربي.
     */
    private const NAMES = [
        'harfiyoon' => 'Al Muhtarifoon',
        'sultan-law' => 'Sultan Al Balushi Law Firm & Legal Consultancy',
        'alyasi-ai-voice' => 'Salem Al Hajri',
        'karam-al-nakhil-ad' => 'Karam Al Nakhil',
    ];

    public function up(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            $table->string('client_name_en')->nullable()->after('client_name');
        });

        foreach (self::NAMES as $slug => $name) {
            DB::table('works')->where('slug', $slug)->update(['client_name_en' => $name]);
        }
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            $table->dropColumn('client_name_en');
        });
    }
};

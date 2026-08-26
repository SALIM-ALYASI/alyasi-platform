<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_context', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();
            $table->decimal('value', 10, 4);
            $table->string('unit')->nullable();
            $table->string('note')->nullable();
            $table->timestamp('verified_at');

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Seed القيم المؤكدة فقط
        |--------------------------------------------------------------------------
        | usd_to_omr وvat_rate ثابتان ومؤكدان (ربط الريال العماني بالدولار،
        | ونسبة ضريبة القيمة المضافة الرسمية). customs_electronics و
        | shipping_estimate_usd متروكان بدون قيمة عمداً -- لا تخمين لرقم
        | جمركي/شحن غير مؤكد، يُضاف لاحقاً من بيانات حقيقية.
        */
        DB::table('market_context')->insert([
            [
                'key' => 'usd_to_omr',
                'value' => 0.3845,
                'unit' => 'OMR per USD',
                'note' => 'الريال العماني مربوط بالدولار الأمريكي بسعر ثابت.',
                'verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'vat_rate',
                'value' => 5.0000,
                'unit' => 'percent',
                'note' => 'ضريبة القيمة المضافة الرسمية في عُمان.',
                'verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('market_context');
    }
};

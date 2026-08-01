<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permalinks', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | العنصر المرتبط
            |--------------------------------------------------------------------------
            |
            | أمثلة linkable_type:
            | service
            | news
            | project
            | page
            |
            */

            $table->string('linkable_type', 50);
            $table->unsignedBigInteger('linkable_id');

            /*
            |--------------------------------------------------------------------------
            | اللغة والرابط الدائم
            |--------------------------------------------------------------------------
            */

            $table->string('locale', 5);
            $table->string('slug');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | القيود والفهارس
            |--------------------------------------------------------------------------
            */

            // يمنع تكرار الرابط داخل اللغة نفسها
            $table->unique([
                'locale',
                'slug',
            ]);

            // رابط رسمي واحد لكل عنصر في كل لغة
            $table->unique([
                'linkable_type',
                'linkable_id',
                'locale',
            ]);

            // تسريع جلب رابط العنصر
            $table->index([
                'linkable_type',
                'linkable_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permalinks');
    }
};

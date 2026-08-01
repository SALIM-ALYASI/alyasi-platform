<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | المحتوى العربي
            |--------------------------------------------------------------------------
            */

            $table->string('title_ar');
            $table->text('description_ar');

            /*
            |--------------------------------------------------------------------------
            | المحتوى الإنجليزي
            |--------------------------------------------------------------------------
            */

            $table->string('title_en');
            $table->text('description_en');

            /*
            |--------------------------------------------------------------------------
            | البيانات المشتركة
            |--------------------------------------------------------------------------
            */

            $table->string('image')->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | الفهارس
            |--------------------------------------------------------------------------
            */

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

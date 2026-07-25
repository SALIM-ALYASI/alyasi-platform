<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permalink_redirects', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | الرابط الرسمي الجديد
            |--------------------------------------------------------------------------
            */

            $table->foreignId('permalink_id')
                ->constrained('permalinks')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | الرابط القديم
            |--------------------------------------------------------------------------
            */

            $table->string('locale', 5);
            $table->string('old_slug');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | القيود والفهارس
            |--------------------------------------------------------------------------
            */

            // يمنع تكرار الرابط القديم داخل اللغة
            $table->unique([
                'locale',
                'old_slug',
            ]);

            $table->index('permalink_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permalink_redirects');
    }
};
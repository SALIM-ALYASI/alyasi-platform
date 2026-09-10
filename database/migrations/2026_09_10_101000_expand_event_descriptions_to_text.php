<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_editions', function (Blueprint $table) {
            $table->text('short_description_ar')->nullable()->change();
            $table->text('short_description_en')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('event_editions', function (Blueprint $table) {
            $table->string('short_description_ar', 500)->nullable()->change();
            $table->string('short_description_en', 500)->nullable()->change();
        });
    }
};

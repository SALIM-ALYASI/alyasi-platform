<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_editions', function (Blueprint $table) {
            $table->longText('content_ar')->nullable()->after('short_description_en');
            $table->longText('content_en')->nullable()->after('content_ar');
        });
    }

    public function down(): void
    {
        Schema::table('event_editions', function (Blueprint $table) {
            $table->dropColumn(['content_ar', 'content_en']);
        });
    }
};

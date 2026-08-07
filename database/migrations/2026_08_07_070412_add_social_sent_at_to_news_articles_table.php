<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->timestamp('social_sent_at')
                ->nullable()
                ->after('published_at');

            $table->index('social_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropIndex(['social_sent_at']);
            $table->dropColumn('social_sent_at');
        });
    }
};

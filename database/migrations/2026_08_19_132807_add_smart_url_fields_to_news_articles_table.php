<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->date('publication_date')
                ->nullable()
                ->after('published_at');

            $table->unsignedInteger('daily_sequence')
                ->nullable()
                ->after('publication_date');

            $table->unique(
                ['publication_date', 'daily_sequence'],
                'news_publication_date_sequence_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropUnique(
                'news_publication_date_sequence_unique'
            );

            $table->dropColumn([
                'publication_date',
                'daily_sequence',
            ]);
        });
    }
};

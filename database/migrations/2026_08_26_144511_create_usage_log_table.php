<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_log', function (Blueprint $table) {
            $table->id();

            $table->foreignId('news_article_id')
                ->nullable()
                ->constrained('news_articles')
                ->nullOnDelete();

            $table->string('stage'); // gate | factpack | edit | seo | ...
            $table->string('model');
            $table->unsignedInteger('input_tokens');
            $table->unsignedInteger('output_tokens');
            $table->decimal('cost_usd', 8, 6);

            $table->timestamps();

            $table->index('stage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_log');
    }
};

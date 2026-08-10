<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('article_category_id')
                ->nullable()
                ->constrained('article_categories')
                ->nullOnDelete();

            $table->foreignId('author_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->string('title', 255);
            $table->string('slug', 280)->unique();

            $table->string('excerpt', 500)->nullable();
            $table->longText('content');

            $table->string('featured_image')->nullable();

            /*
             * حالة المقال:
             * draft     = مسودة
             * published = منشور
             * archived  = مؤرشف
             */
            $table->string('status', 30)->default('draft');

            $table->boolean('is_featured')->default(false);

            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('reading_time')->nullable();

            $table->timestamp('published_at')->nullable();

            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('meta_keywords', 255)->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('is_featured');
            $table->index('published_at');
            $table->index(['article_category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};

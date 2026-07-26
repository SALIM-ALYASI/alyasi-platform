<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('news_category_id')
                ->nullable()
                ->constrained('news_categories')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | المحتوى باللغتين
            |--------------------------------------------------------------------------
            */

            $table->string('title_ar', 500);
            $table->string('title_en', 500);

            $table->text('excerpt_ar')->nullable();
            $table->text('excerpt_en')->nullable();

            $table->longText('content_ar')->nullable();
            $table->longText('content_en')->nullable();

            /*
            |--------------------------------------------------------------------------
            | الصورة والمصدر
            |--------------------------------------------------------------------------
            */

            $table->string('image')->nullable();
            $table->string('image_alt_ar')->nullable();
            $table->string('image_alt_en')->nullable();

            $table->string('source_name')->nullable();
            $table->text('source_url')->nullable();
            $table->string('author_name')->nullable();

            /*
            |--------------------------------------------------------------------------
            | حالة النشر
            |--------------------------------------------------------------------------
            */

            $table->string('status', 20)->default('draft');
            $table->boolean('is_breaking')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);

            /*
            |--------------------------------------------------------------------------
            | SEO
            |--------------------------------------------------------------------------
            */

            $table->string('seo_title_ar', 70)->nullable();
            $table->string('seo_title_en', 70)->nullable();
            $table->string('seo_description_ar', 170)->nullable();
            $table->string('seo_description_en', 170)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['news_category_id', 'status']);
            $table->index(['is_breaking', 'published_at']);
            $table->index(['is_featured', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};

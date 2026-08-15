<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title_ar', 500)->nullable()->after('id');
            $table->string('title_en', 500)->nullable()->after('title_ar');

            $table->text('excerpt_ar')->nullable()->after('title_en');
            $table->text('excerpt_en')->nullable()->after('excerpt_ar');

            $table->longText('content_ar')->nullable()->after('excerpt_en');
            $table->longText('content_en')->nullable()->after('content_ar');

            $table->string('meta_title_ar', 255)->nullable()->after('content_en');
            $table->string('meta_title_en', 255)->nullable()->after('meta_title_ar');

            $table->string('meta_description_ar', 500)->nullable()->after('meta_title_en');
            $table->string('meta_description_en', 500)->nullable()->after('meta_description_ar');

            $table->string('meta_keywords_ar', 255)->nullable()->after('meta_description_en');
            $table->string('meta_keywords_en', 255)->nullable()->after('meta_keywords_ar');
        });

        $this->backfill();

        Schema::table('articles', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'title',
                'slug',
                'excerpt',
                'content',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ]);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->string('title_ar', 500)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title', 255)->nullable()->after('author_id');
            $table->string('slug', 280)->nullable()->after('title');
            $table->string('excerpt', 500)->nullable()->after('slug');
            $table->longText('content')->nullable()->after('excerpt');
            $table->string('meta_title', 255)->nullable()->after('content');
            $table->string('meta_description', 500)->nullable()->after('meta_title');
            $table->string('meta_keywords', 255)->nullable()->after('meta_description');
        });

        DB::table('articles')->orderBy('id')->chunkById(100, function ($articles) {
            foreach ($articles as $article) {
                DB::table('articles')->where('id', $article->id)->update([
                    'title' => $article->title_ar,
                    'excerpt' => $article->excerpt_ar,
                    'content' => $article->content_ar,
                    'meta_title' => $article->meta_title_ar,
                    'meta_description' => $article->meta_description_ar,
                    'meta_keywords' => $article->meta_keywords_ar,
                    'slug' => DB::table('permalinks')
                        ->where('linkable_type', 'article')
                        ->where('linkable_id', $article->id)
                        ->where('locale', 'ar')
                        ->value('slug') ?? ('article-'.$article->id),
                ]);
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->unique('slug');
            $table->dropColumn([
                'title_ar',
                'title_en',
                'excerpt_ar',
                'excerpt_en',
                'content_ar',
                'content_en',
                'meta_title_ar',
                'meta_title_en',
                'meta_description_ar',
                'meta_description_en',
                'meta_keywords_ar',
                'meta_keywords_en',
            ]);
        });
    }

    /**
     * نسخ بيانات المقالات الحالية (عربية) إلى الأعمدة الجديدة،
     * وإنشاء رابط دائم (Permalink) عربي لكل مقال بنفس الـ slug القديم.
     */
    private function backfill(): void
    {
        DB::table('articles')->orderBy('id')->chunkById(100, function ($articles) {
            foreach ($articles as $article) {
                DB::table('articles')->where('id', $article->id)->update([
                    'title_ar' => $article->title,
                    'excerpt_ar' => $article->excerpt,
                    'content_ar' => $article->content,
                    'meta_title_ar' => $article->meta_title,
                    'meta_description_ar' => $article->meta_description,
                    'meta_keywords_ar' => $article->meta_keywords,
                ]);

                DB::table('permalinks')->insert([
                    'linkable_type' => 'article',
                    'linkable_id' => $article->id,
                    'locale' => 'ar',
                    'slug' => $this->uniqueSlug($article->slug ?: ('article-'.$article->id)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    private function uniqueSlug(string $baseSlug): string
    {
        $baseSlug = Str::slug($baseSlug) ?: 'article';
        $slug = $baseSlug;
        $counter = 2;

        while (DB::table('permalinks')->where('locale', 'ar')->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
};

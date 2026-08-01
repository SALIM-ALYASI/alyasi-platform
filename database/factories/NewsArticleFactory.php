<?php

namespace Database\Factories;

use App\Models\NewsArticle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsArticle>
 */
class NewsArticleFactory extends Factory
{
    protected $model = NewsArticle::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title_ar' => fake()->sentence(),
            'title_en' => fake()->sentence(),
            'excerpt_ar' => fake()->text(150),
            'excerpt_en' => fake()->text(150),
            'content_ar' => fake()->paragraphs(3, true),
            'content_en' => fake()->paragraphs(3, true),
            'source_name' => fake()->company(),
            'source_url' => fake()->unique()->url(),
            'author_name' => fake()->name(),
            'status' => NewsArticle::STATUS_PUBLISHED,
            'is_breaking' => false,
            'is_featured' => false,
            'published_at' => now(),
            'views_count' => 0,
        ];
    }
}

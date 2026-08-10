<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\NewsArticle
 */
class NewsArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'title' => [
                'ar' => $this->title_ar,
                'en' => $this->title_en,
            ],

            'excerpt' => [
                'ar' => $this->excerpt_ar,
                'en' => $this->excerpt_en,
            ],

            'content' => [
                'ar' => $this->content_ar,
                'en' => $this->content_en,
            ],

            'image' => $this->image ? media_url($this->image) : null,

            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => [
                    'ar' => $this->category->name_ar,
                    'en' => $this->category->name_en,
                ],
                'slug' => $this->category->slug,
            ]),

            'source_name' => $this->source_name,
            'source_url' => $this->source_url,
            'author_name' => $this->author_name,

            'status' => $this->status,
            'is_breaking' => $this->is_breaking,
            'is_featured' => $this->is_featured,
            'views_count' => $this->views_count,

            'published_at' => $this->published_at?->toIso8601String(),

            'slug' => [
                'ar' => $this->whenLoaded('permalinks', fn () => $this->permalinks->firstWhere('locale', 'ar')?->slug),
                'en' => $this->whenLoaded('permalinks', fn () => $this->permalinks->firstWhere('locale', 'en')?->slug),
            ],

            'url' => $this->whenLoaded('permalinks', function () {
                $arSlug = $this->permalinks->firstWhere('locale', 'ar')?->slug;
                $enSlug = $this->permalinks->firstWhere('locale', 'en')?->slug;

                return [
                    'ar' => $arSlug ? route('news.show', $arSlug) : null,
                    'en' => $enSlug ? route('news.show', $enSlug) : null,
                ];
            }),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

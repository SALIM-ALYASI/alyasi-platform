<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Service
 */
class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'title' => [
                'ar' => $this->title_ar,
                'en' => $this->title_en,
            ],

            'description' => [
                'ar' => $this->description_ar,
                'en' => $this->description_en,
            ],

            'image' => $this->image
                ? asset($this->image)
                : null,

            'slug' => [
                'ar' => $this->slug('ar'),
                'en' => $this->slug('en'),
            ],

            'url' => [
                'ar' => $this->slug('ar')
                    ? route('services.show', $this->slug('ar'))
                    : null,
                'en' => $this->slug('en')
                    ? route('services.show', $this->slug('en'))
                    : null,
            ],

            'is_active' => $this->is_active,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

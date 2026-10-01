<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductLaunch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductLaunchIngestController extends Controller
{
    /**
     * يستقبل دفعة إطلاقات منتجات من product_watch.py (سيرفر البيت) ويخزّنها
     * -- idempotent عبر link_hash، نفس الخبر المُرسل مرتين ما يتكرر بقاعدة
     * البيانات.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.company' => ['required', 'string', 'max:50'],
            'items.*.title' => ['required', 'string', 'max:500'],
            'items.*.link' => ['required', 'url', 'max:2000'],
            'items.*.published_at' => ['nullable', 'date'],
        ]);

        $created = 0;

        foreach ($validated['items'] as $item) {
            $launch = ProductLaunch::query()->firstOrCreate(
                ['link_hash' => hash('sha256', $item['link'])],
                [
                    'company' => $item['company'],
                    'title' => $item['title'],
                    'link' => $item['link'],
                    'published_at' => $item['published_at'] ?? null,
                ]
            );

            if ($launch->wasRecentlyCreated) {
                $created++;
            }
        }

        return response()->json(['success' => true, 'created' => $created]);
    }
}

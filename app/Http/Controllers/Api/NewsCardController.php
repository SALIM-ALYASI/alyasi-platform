<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * صور الأخبار للمنصات:
 * - store: مسار الصور (بوت الأخبار/n8n) يرفع البطاقة الجاهزة بعد نشر الخبر.
 * - pending: وكيل Grok يسحب البطاقات الجديدة لـX.
 * - result: Grok يرجع رابط التغريدة أو سبب الفشل (يمنع إعادة نفس البطاقة).
 */
class NewsCardController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'source_type' => ['required', Rule::in(NewsCard::SOURCES)],
            'source_id' => ['required', 'integer', 'min:1'],
            'title_ar' => ['required', 'string', 'max:500'],
            'title_en' => ['nullable', 'string', 'max:500'],
            'url' => ['required', 'url', 'max:1000', 'starts_with:https://alyasi.dev/'],
            'image_ar' => ['required', 'file', 'mimes:png', 'max:6144'],
            'image_en' => ['nullable', 'file', 'mimes:png', 'max:6144'],
        ]);

        $existing = NewsCard::query()
            ->where('source_type', $data['source_type'])
            ->where('source_id', $data['source_id'])
            ->first();

        if ($existing) {
            return response()->json(['success' => true, 'idempotent' => true, 'data' => $this->present($existing)]);
        }

        $base = $data['source_type'].'-'.$data['source_id'];
        $card = NewsCard::query()->create([
            'source_type' => $data['source_type'],
            'source_id' => $data['source_id'],
            'title_ar' => trim($data['title_ar']),
            'title_en' => filled($data['title_en'] ?? null) ? trim($data['title_en']) : null,
            'url' => $data['url'],
            'image_ar' => $this->saveImage($request->file('image_ar'), $base.'-ar'),
            'image_en' => $request->hasFile('image_en') ? $this->saveImage($request->file('image_en'), $base.'-en') : null,
            'text_x' => NewsCard::buildXText($data['title_ar'], $data['title_en'] ?? null, $data['url']),
        ]);

        return response()->json(['success' => true, 'idempotent' => false, 'data' => $this->present($card)], 201);
    }

    public function pending(Request $request): JsonResponse
    {
        $request->validate(['platform' => ['nullable', Rule::in(['x'])]]);

        $cards = NewsCard::query()
            ->where('x_status', 'pending')
            ->where('created_at', '>=', now()->subHours(48))
            ->orderBy('id')
            ->limit(10)
            ->get();

        return response()->json(['data' => $cards->map(fn (NewsCard $card) => $this->present($card))->values()]);
    }

    public function result(Request $request, NewsCard $newsCard): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['posted', 'failed', 'skipped'])],
            'tweet_url' => ['nullable', 'required_if:status,posted', 'url', 'max:500', 'regex:#^https://(x\.com|twitter\.com)/#'],
            'error' => ['nullable', 'string', 'max:500'],
        ]);

        $newsCard->update([
            'x_status' => $data['status'],
            'x_tweet_url' => $data['tweet_url'] ?? $newsCard->x_tweet_url,
            'x_note' => isset($data['error']) ? Str::limit($data['error'], 480) : null,
            'x_result_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $this->present($newsCard)]);
    }

    private function saveImage(UploadedFile $file, string $name): string
    {
        $directory = 'uploads/news-cards/'.now()->format('Ym');
        File::ensureDirectoryExists(public_path($directory), 0755, true);
        $fileName = $name.'-'.Str::lower(Str::random(8)).'.png';
        $file->move(public_path($directory), $fileName);

        return $directory.'/'.$fileName;
    }

    private function present(NewsCard $card): array
    {
        return [
            'id' => $card->id,
            'source_type' => $card->source_type,
            'image_url' => asset($card->image_ar),
            'image_url_en' => $card->image_en ? asset($card->image_en) : null,
            'text' => $card->text_x,
            'title_ar' => $card->title_ar,
            'title_en' => $card->title_en,
            'url' => $card->url,
            'x_status' => $card->x_status,
            'x_tweet_url' => $card->x_tweet_url,
            'created_at' => $card->created_at?->toIso8601String(),
        ];
    }
}

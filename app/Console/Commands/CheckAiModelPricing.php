<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Support\WhatsAppAlerts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * يراقب أسعار نماذج Gemini المستخدمة فعليًا ببوت الأخبار (news-bot-v2)
 * من صفحة التسعير الرسمية، وينبّه عبر واتساب عند أي تغيير -- عشان نعرف
 * نعدّل لإصدار مناسب لو زاد السعر أو صار فيه بديل أرخص.
 */
class CheckAiModelPricing extends Command
{
    protected $signature = 'ai-pricing:check';

    protected $description = 'يتحقق من تغيّر أسعار نماذج Gemini المستخدمة وينبّه عبر واتساب عند أي تغيير';

    private const PRICING_URL = 'https://ai.google.dev/gemini-api/docs/pricing';

    /**
     * معرّف النموذج (نفس القيمة بـconfig.yaml لبوت الأخبار) => اسم العرض.
     */
    private const TRACKED_MODELS = [
        'gemini-3.1-flash-lite' => 'Gemini 3.1 Flash-Lite',
        'gemini-3.5-flash' => 'Gemini 3.5 Flash',
    ];

    public function handle(): int
    {
        try {
            $html = Http::timeout(20)->get(self::PRICING_URL)->body();
        } catch (\Throwable $e) {
            Log::warning('تعذّر جلب صفحة أسعار Gemini.', ['error' => $e->getMessage()]);

            return self::SUCCESS;
        }

        foreach (self::TRACKED_MODELS as $modelId => $label) {
            $this->checkModel($html, $modelId, $label);
        }

        return self::SUCCESS;
    }

    private function checkModel(string $html, string $modelId, string $label): void
    {
        $stateKey = "ai_pricing_{$modelId}";
        $notFoundKey = "ai_pricing_{$modelId}_not_found_alerted";
        $prices = $this->extractPrices($html, $modelId);

        if (! $prices) {
            // الصفحة تغيّرت هيكليًا أو النموذج أُزيل/أُعيدت تسميته -- تنبيه
            // مرة وحدة بس (بدون تكرار يومي) عشان المستخدم يتحقق يدويًا.
            if (Setting::get($notFoundKey) !== '1') {
                WhatsAppAlerts::send(
                    "ما قدرت ألقى سعر {$label} ({$modelId}) بصفحة تسعير Gemini -- ممكن النموذج أُزيل/أُعيد تسميته، أو تغيّر شكل الصفحة. تحقق يدويًا: ".self::PRICING_URL
                );
                Setting::set($notFoundKey, '1');
            }

            return;
        }

        Setting::set($notFoundKey, '0');

        [$input, $output] = $prices;
        $previousRaw = Setting::get($stateKey);
        $previous = $previousRaw ? json_decode($previousRaw, true) : null;

        if ($previous && ((float) $previous['input'] !== $input || (float) $previous['output'] !== $output)) {
            WhatsAppAlerts::send(
                "تغيّر سعر {$label} ({$modelId}) لكل مليون توكن:\n"
                ."الإدخال: \${$previous['input']} → \${$input}\n"
                ."الإخراج: \${$previous['output']} → \${$output}"
            );
        }

        Setting::set($stateKey, json_encode(['input' => $input, 'output' => $output]));
    }

    /**
     * @return array{0: float, 1: float}|null [input_price, output_price]
     */
    private function extractPrices(string $html, string $modelId): ?array
    {
        if (! preg_match('/id="'.preg_quote($modelId, '/').'"/', $html, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        // 6000 حرف كافية تغطي قسم "Standard" كامل (مؤكد بالفحص اليدوي)
        // قبل ما تبدأ أقسام Batch/Live المكررة لنفس التسميات.
        $segment = substr($html, $m[0][1], 6000);
        $plain = preg_replace('/\s+/', ' ', strip_tags($segment));

        if (
            ! preg_match('/Input price.*?\$([0-9.]+)/s', $plain, $inputMatch)
            || ! preg_match('/Output price[^$]*?\$([0-9.]+)/s', $plain, $outputMatch)
        ) {
            return null;
        }

        return [(float) $inputMatch[1], (float) $outputMatch[1]];
    }
}

<?php

namespace App\Jobs;

use App\Models\PageVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResolveVisitCountry implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * لا داعي لإعادة المحاولة كثيرًا على خدمة خارجية مجانية محدودة المعدل.
     */
    public int $tries = 1;

    public function __construct(
        private readonly int $pageVisitId
    ) {}

    /**
     * تحديد بلد الزيارة من عنوان الـ IP بشكل غير متزامن حتى لا يبطئ استجابة الصفحة.
     */
    public function handle(): void
    {
        $visit = PageVisit::query()->find($this->pageVisitId);

        if (! $visit || blank($visit->ip_address)) {
            return;
        }

        if (! $this->isPublicIp($visit->ip_address)) {
            $visit->update(['country_name' => 'محلي']);

            return;
        }

        // نفس العنوان انعرف بلده قبل؟ ناخذه من الزيارات السابقة بدل ما نسأل الخدمة.
        $known = PageVisit::query()
            ->where('ip_address', $visit->ip_address)
            ->whereNotNull('country_code')
            ->latest('id')
            ->first(['country_code', 'country_name']);

        if ($known) {
            $visit->update(['country_code' => $known->country_code, 'country_name' => $known->country_name]);

            return;
        }

        try {
            // الخدمة المجانية تسمح 45 طلب بالدقيقة -- نحفظ الجواب يوم عشان التكرار.
            $data = Cache::remember('visit-country:'.$visit->ip_address, now()->addDay(), function () use ($visit) {
                $response = Http::timeout(4)
                    ->get("http://ip-api.com/json/{$visit->ip_address}", [
                        'fields' => 'status,country,countryCode',
                    ]);

                return $response->successful() && $response->json('status') === 'success'
                    ? ['country_code' => $response->json('countryCode'), 'country_name' => $response->json('country')]
                    : null;
            });

            if (! $data) {
                Cache::forget('visit-country:'.$visit->ip_address);

                return;
            }

            $visit->update($data);
        } catch (\Throwable $e) {
            Log::warning('تعذر تحديد بلد الزيارة', [
                'ip' => $visit->ip_address,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * استبعاد عناوين الـ IP المحلية/الخاصة من طلب الموقع الجغرافي.
     */
    private function isPublicIp(string $ip): bool
    {
        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}

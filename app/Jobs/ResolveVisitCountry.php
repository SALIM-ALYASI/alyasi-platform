<?php

namespace App\Jobs;

use App\Models\PageVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

        try {
            $response = Http::timeout(4)
                ->get("http://ip-api.com/json/{$visit->ip_address}", [
                    'fields' => 'status,country,countryCode',
                ]);

            if (! $response->successful() || $response->json('status') !== 'success') {
                return;
            }

            $visit->update([
                'country_code' => $response->json('countryCode'),
                'country_name' => $response->json('country'),
            ]);
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

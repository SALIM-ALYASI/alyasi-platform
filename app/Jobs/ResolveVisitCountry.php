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
use Illuminate\Support\Facades\RateLimiter;

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
     * للزيارات المصنّفة نسأل كمان: العنوان لمركز بيانات أو بروكسي؟ لو نعم تصير
     * «مشتبه بها» (ما تنحذف -- ناس حقيقيين يستخدمون VPN أحيانًا).
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

        if ($visit->traffic_class === null) {
            $this->resolveLegacy($visit);

            return;
        }

        $info = $this->lookup($visit->ip_address);

        if (! $info) {
            return;
        }

        if ($info['hosting']) {
            $visit->markSuspect('datacenter');
        }

        if ($info['proxy']) {
            $visit->markSuspect('proxy');
        }

        // بلد Cloudflare (لو موجود) أدق من قاعدة ip-api، فما نغيّره.
        $visit->country_code ??= $info['country_code'];
        $visit->country_name = $visit->country_code === $info['country_code']
            ? $info['country_name']
            : (locale_get_display_region('-'.$visit->country_code, 'en') ?: $info['country_name']);

        $visit->save();
    }

    /**
     * @return array{country_code: ?string, country_name: ?string, hosting: bool, proxy: bool}|null
     */
    private function lookup(string $ip): ?array
    {
        $key = 'visit-ip:'.$ip;

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        // الخدمة المجانية تسمح 45 طلب بالدقيقة -- نخلي هامش ونحفظ الجواب أسبوع.
        if (RateLimiter::tooManyAttempts('ip-api', 40)) {
            return null;
        }

        RateLimiter::hit('ip-api', 60);

        try {
            $response = Http::timeout(4)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,country,countryCode,hosting,proxy',
            ]);
        } catch (\Throwable $e) {
            Log::warning('تعذر فحص عنوان الزيارة', ['ip' => $ip, 'error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful() || $response->json('status') !== 'success') {
            return null;
        }

        $info = [
            'country_code' => $response->json('countryCode'),
            'country_name' => $response->json('country'),
            'hosting' => (bool) $response->json('hosting'),
            'proxy' => (bool) $response->json('proxy'),
        ];

        Cache::put($key, $info, now()->addWeek());

        return $info;
    }

    /**
     * الزيارات القديمة غير المصنّفة: البلد فقط، مثل قبل.
     */
    private function resolveLegacy(PageVisit $visit): void
    {
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

        $info = $this->lookup($visit->ip_address);

        if ($info) {
            $visit->update(['country_code' => $info['country_code'], 'country_name' => $info['country_name']]);
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

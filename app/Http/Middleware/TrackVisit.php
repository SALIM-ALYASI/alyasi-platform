<?php

namespace App\Http\Middleware;

use App\Jobs\ResolveVisitCountry;
use App\Models\PageVisit;
use App\Support\Analytics\CloudflareIps;
use App\Support\Analytics\VisitClassifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class TrackVisit
{
    /**
     * تسجيل زيارات الصفحات العامة (بدون لوحة التحكم أو API) مع تصنيفها:
     * ولا زيارة تنحذف -- البوتات وسالم والفحوصات تنحفظ بتصنيفها وتنستبعد
     * من الأرقام بلوحة التحكم. البلد وفحص مراكز البيانات بعد الاستجابة.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 200 || ! $this->shouldTrack($request)) {
            return $response;
        }

        [$class, $reasons] = VisitClassifier::classify($request);
        $visitorId = $this->visitorId($request, $class, $response);

        // $request->ip() يرجع عنوان الزائر الحقيقي لما يكون الطلب من Cloudflare
        // (trustProxies ببootstrap/app.php)؛ REMOTE_ADDR الخام ينحفظ للتدقيق.
        $proxyIp = $request->server('REMOTE_ADDR');
        $viaCloudflare = CloudflareIps::contains($proxyIp);
        $country = strtoupper((string) $request->header('CF-IPCountry'));

        $visit = PageVisit::query()->create([
            // الروابط العربية المرمّزة (%D9%81…) ممكن تتعدى 255 حرف وتطيّح الصفحة بخطأ 500.
            'path' => substr('/'.ltrim($request->path(), '/'), 0, 255),
            'ip_address' => $request->ip(),
            'proxy_ip' => $proxyIp !== $request->ip() ? $proxyIp : null,
            // بلد Cloudflare موثوق فقط لما الطلب جاي منها فعلًا (XX/T1 = مجهول/Tor).
            'country_code' => $viaCloudflare && preg_match('/^[A-Z]{2}$/', $country) && ! in_array($country, ['XX', 'T1'], true) ? $country : null,
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'traffic_class' => $class,
            'class_reasons' => $reasons ? implode(',', $reasons) : null,
            'visitor_id' => $visitorId,
        ]);

        // البوتات المعلنة وسالم والفحوصات ما تحتاج بلد ولا فحص مركز بيانات.
        if (in_array($class, [VisitClassifier::HUMAN, VisitClassifier::SUSPECT], true)) {
            // بعد ما تنرسل الصفحة للزائر (بدون طابور): الاستضافة ما فيها
            // queue worker، فالطابور كان يتكدّس وما يتحدد أي بلد.
            ResolveVisitCountry::dispatchAfterResponse($visit->id);
        }

        return $response;
    }

    private function shouldTrack(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        return ! $request->is('admin*', 'api*', 'locale/*', 'analytics/*', 'up');
    }

    /**
     * معرّف عشوائي بكوكي الطرف الأول عشان نعرف الزائر اللي يرجع بيوم ثاني.
     * ما فيه أي بيانات شخصية، والبوتات ما ناخذ لها كوكي.
     */
    private function visitorId(Request $request, string $class, Response $response): ?string
    {
        if (in_array($class, [VisitClassifier::BOT, VisitClassifier::INTERNAL], true)) {
            return null;
        }

        $name = config('analytics.visitor_cookie');
        $existing = $request->cookie($name);

        if (is_string($existing) && preg_match('/^[a-z0-9]{20,32}$/', $existing)) {
            return $existing;
        }

        $id = strtolower((string) Str::ulid());
        $response->headers->setCookie(new Cookie($name, $id, now()->addYears(2), '/', null, str_starts_with((string) config('app.url'), 'https'), true, false, Cookie::SAMESITE_LAX));

        return $id;
    }
}

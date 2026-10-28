<?php

namespace App\Http\Controllers;

use App\Models\PageEvent;
use App\Models\PageVisit;
use App\Support\Analytics\VisitClassifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class AnalyticsController extends Controller
{
    /**
     * رابط سالم الخاص: يفتحه مرة من كل جهاز عشان زياراته ما تنحسب.
     * ?do=exclude يستبعد الجهاز، ?do=include يرجّعه للإحصائيات.
     */
    public function device(Request $request): Response
    {
        $key = (string) config('analytics.owner_key');

        abort_if($key === '' || ! hash_equals($key, (string) $request->query('key')), 404);

        $cookieName = config('analytics.owner_cookie');
        $excluded = $request->cookie($cookieName) === '1';
        $action = $request->query('do');

        if ($action === 'exclude') {
            Cookie::queue(Cookie::forever($cookieName, '1', '/', null, null, true, false, 'lax'));
            $excluded = true;
        } elseif ($action === 'include') {
            Cookie::queue(Cookie::forget($cookieName));
            $excluded = false;
        }

        return response()
            ->view('analytics.device', ['excluded' => $excluded, 'key' => $key, 'changed' => in_array($action, ['exclude', 'include'], true)])
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * أحداث التفاعل من الصفحة (navigator.sendBeacon): وصول لنهاية المقال،
     * تشغيل صوت، إكمال صوت. التصنيف ينورث من آخر زيارة لنفس الزائر.
     */
    public function event(Request $request): Response
    {
        // «view»: المتصفح شغّل جافاسكربت ورجّع الكوكي والصفحة ظاهرة ثانيتين —
        // يأكد آخر زيارة لنفس الزائر والصفحة (ما ينحفظ كحدث تفاعل).
        if ($request->input('type') === 'view') {
            return $this->confirmView($request);
        }

        // sendBeacon ما يقرأ الرد، فما نحتاج رسائل خطأ ولا تحويل للخلف.
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'in:'.implode(',', PageEvent::TYPES)],
            'path' => ['required', 'string', 'max:2000'],
            'label' => ['nullable', 'string', 'max:2000'],
            'seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
        ]);

        abort_if($validator->fails(), 422);

        $data = $validator->validated();

        [$class] = VisitClassifier::classify($request, isPageView: false);

        $visitorId = $request->cookie(config('analytics.visitor_cookie'));
        $visitorId = is_string($visitorId) && preg_match('/^[a-z0-9]{20,32}$/', $visitorId) ? $visitorId : null;

        // الزيارة صُنّفت أدق (فحص مركز البيانات بعد الاستجابة) -- ناخذ تصنيفها.
        if ($visitorId && $class === VisitClassifier::HUMAN) {
            $class = PageVisit::query()
                ->where('visitor_id', $visitorId)
                ->latest('id')
                ->value('traffic_class') ?? $class;
        }

        PageEvent::query()->create([
            'visitor_id' => $visitorId,
            'path' => substr('/'.ltrim((string) parse_url($data['path'], PHP_URL_PATH), '/'), 0, 255),
            'type' => $data['type'],
            'label' => isset($data['label']) ? substr((string) parse_url($data['label'], PHP_URL_PATH), 0, 255) : null,
            'seconds' => $data['seconds'] ?? null,
            'traffic_class' => $class,
        ]);

        return response()->noContent();
    }

    private function confirmView(Request $request): Response
    {
        $visitorId = $request->cookie(config('analytics.visitor_cookie'));
        $path = substr('/'.ltrim((string) parse_url((string) $request->input('path'), PHP_URL_PATH), '/'), 0, 255);

        if (is_string($visitorId) && preg_match('/^[a-z0-9]{20,32}$/', $visitorId)) {
            PageVisit::query()
                ->where('visitor_id', $visitorId)
                ->where('path', $path)
                ->whereNull('confirmed_at')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->latest('id')
                ->limit(1)
                ->update(['confirmed_at' => now()]);
        }

        return response()->noContent();
    }
}

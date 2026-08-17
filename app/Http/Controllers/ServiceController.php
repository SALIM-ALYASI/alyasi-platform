<?php

namespace App\Http\Controllers;

use App\Models\Permalink;
use App\Models\PermalinkRedirect;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * عرض الخدمات المفعلة في الموقع العام.
     */
    public function index(Request $request): View
    {
        $services = Service::query()
            ->with('permalinks')
            ->where('is_active', true)
            ->search($request->string('search')->toString())
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view(
            'services.index',
            compact('services')
        );
    }

    /**
     * عرض خدمة واحدة بواسطة الرابط الدائم.
     */
    public function show(string $slug): View|RedirectResponse
    {
        $locale = app()->getLocale();

        $permalink = $this->findServicePermalink($slug, $locale);

        if (! $permalink) {
            $otherLocale = $locale === 'ar' ? 'en' : 'ar';
            $otherLocalePermalink = $this->findServicePermalink($slug, $otherLocale);

            if ($otherLocalePermalink) {
                return redirect()->route(
                    $otherLocale === 'en' ? 'services.show.en' : 'services.show',
                    ['slug' => $otherLocalePermalink->slug],
                    301
                );
            }

            return $this->redirectFromOldSlug($slug, $locale);
        }

        $service = $permalink->linkable;

        abort_unless(
            $service instanceof Service && $service->is_active,
            404
        );

        $service->loadMissing([
            'permalinks',
            'approvedReviews',
            'works' => fn ($query) => $query
                ->where('is_active', true)
                ->limit(3),
        ]);

        return view(
            'services.show',
            compact('service', 'permalink')
        );
    }

    /**
     * البحث عن الرابط الحالي للخدمة، مقيّدًا باللغة المطلوبة فقط —
     * حتى لا يظهر slug اللغة الأخرى بنفس المسار (200 مكرر).
     */
    private function findServicePermalink(
        string $slug,
        string $locale
    ): ?Permalink {
        return Permalink::query()
            ->with('linkable')
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->whereHasMorph(
                'linkable',
                [Service::class],
                fn ($query) => $query->where('is_active', true)
            )
            ->first();
    }

    /**
     * تحويل الروابط القديمة إلى الرابط الحالي للخدمة.
     */
    private function redirectFromOldSlug(
        string $slug,
        string $locale
    ): RedirectResponse {
        $redirect = PermalinkRedirect::query()
            ->with('permalink.linkable')
            ->where('old_slug', $slug)
            ->orderByRaw(
                'CASE WHEN locale = ? THEN 0 ELSE 1 END',
                [$locale]
            )
            ->first();

        abort_unless($redirect?->permalink, 404);

        $service = $redirect->permalink->linkable;

        abort_unless(
            $service instanceof Service && $service->is_active,
            404
        );

        return redirect()->route(
            'services.show',
            ['slug' => $redirect->permalink->slug],
            301
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\EventEdition;
use App\Models\Permalink;
use App\Models\PermalinkRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventEditionController extends Controller
{
    /**
     * صفحة نسخة مؤتمر (رابط دائم عبر Permalink، نفس نمط NewsController::show()).
     */
    public function show(string $slug): View|RedirectResponse
    {
        $locale = app()->getLocale();

        $permalink = Permalink::query()
            ->with('linkable')
            ->where('locale', $locale)
            ->where('linkable_type', 'event_edition')
            ->where('slug', $slug)
            ->first();

        if (! $permalink) {
            $otherLocale = $locale === 'ar' ? 'en' : 'ar';

            $otherLocalePermalink = Permalink::query()
                ->where('locale', $otherLocale)
                ->where('linkable_type', 'event_edition')
                ->where('slug', $slug)
                ->first();

            if ($otherLocalePermalink) {
                return redirect()->route(
                    $otherLocale === 'en' ? 'event_editions.show.en' : 'event_editions.show',
                    ['slug' => $otherLocalePermalink->slug],
                    301
                );
            }

            return $this->redirectFromOldSlug($slug, $locale);
        }

        $edition = $permalink->linkable;

        abort_unless($edition instanceof EventEdition, 404);

        $isPublished = EventEdition::query()
            ->published()
            ->whereKey($edition->getKey())
            ->exists();

        abort_unless($isPublished, 404);

        return view('events.show', compact('edition'));
    }

    /**
     * تحويل ٣٠١ من رابط قديم لو تغيّر السلق.
     */
    private function redirectFromOldSlug(string $slug, string $locale): RedirectResponse
    {
        $redirect = PermalinkRedirect::query()
            ->with('permalink.linkable')
            ->where('old_slug', $slug)
            ->orderByRaw('CASE WHEN locale = ? THEN 0 ELSE 1 END', [$locale])
            ->first();

        abort_unless($redirect?->permalink, 404);

        $edition = $redirect->permalink->linkable;

        abort_unless($edition instanceof EventEdition, 404);

        $isPublished = EventEdition::query()
            ->published()
            ->whereKey($edition->getKey())
            ->exists();

        abort_unless($isPublished, 404);

        return redirect()->to($redirect->permalink->url(), 301);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;

class CommunityController extends Controller
{
    /**
     * قسم المجتمع العام تم دمجه في مركز المؤتمرات والفعاليات.
     * نحافظ على الرابط القديم بتحويل 301 حتى لا نخسر الروابط المفهرسة.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('events.index', [], 301);
    }

    /**
     * روابط منشورات المجتمع القديمة تتحول إلى مركز الفعاليات.
     * مؤتمر Apple 2026 له مركز سلسلة مخصص، فنوجهه مباشرة إلى Apple.
     */
    public function show(CommunityPost $communityPost): RedirectResponse
    {
        if ($communityPost->slug === 'motmr-abl-surprise-and-shine-iphone-18-september-2026') {
            return redirect()->route(
                'event_editions.show',
                ['slug' => 'apple'],
                301
            );
        }

        return redirect()->route('events.index', [], 301);
    }
}

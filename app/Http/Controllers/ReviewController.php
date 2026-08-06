<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProtectsAgainstAbuse;
use App\Models\Service;
use App\Models\Work;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ProtectsAgainstAbuse;

    /**
     * إضافة تقييم جديد على عمل (بانتظار المراجعة).
     */
    public function storeForWork(Request $request, Work $work): RedirectResponse
    {
        return $this->store($request, $work, 'works.show', $work->slug);
    }

    /**
     * إضافة تقييم جديد على خدمة (بانتظار المراجعة).
     */
    public function storeForService(Request $request, Service $service): RedirectResponse
    {
        return $this->store($request, $service, 'services.show', $service->slug());
    }

    /**
     * منطق حفظ التقييم المشترك بين الأعمال والخدمات.
     */
    private function store(
        Request $request,
        Model $reviewable,
        string $redirectRoute,
        ?string $redirectParam
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $deviceToken = $this->resolveDeviceToken($request);

        $redirect = redirect()
            ->route($redirectRoute, $redirectParam)
            ->withFragment('reviews');

        if ($this->isVisitorBlocked($deviceToken, $request->ip())) {
            return $redirect->withErrors([
                'body' => __('reviews.review_blocked'),
            ]);
        }

        $reviewable->reviews()->create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'rating' => $validated['rating'],
            'body' => $validated['body'],
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'device_token' => $deviceToken,
        ]);

        return $redirect
            ->with('success', __('reviews.review_submitted'))
            ->withCookie($this->deviceTokenCookie($deviceToken));
    }
}

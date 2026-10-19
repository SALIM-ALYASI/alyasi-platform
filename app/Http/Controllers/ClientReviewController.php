<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NotifiesManagerBot;
use App\Http\Controllers\Concerns\ProtectsAgainstAbuse;
use App\Models\Work;
use App\Support\WhatsAppAlerts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * صفحة مؤقتة يرسلها سالم لعميل مشروع يطلب تقييمه: رابط موقّع ينتهي (reviews:link)،
 * والتقييم يدخل مراجعة لوحة التحكم مثل أي تقييم -- ما ينشر إلا بعد الاعتماد.
 */
class ClientReviewController extends Controller
{
    use NotifiesManagerBot;
    use ProtectsAgainstAbuse;

    public function show(Work $work): Response
    {
        return response()
            ->view('reviews.request', ['work' => $work])
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request, Work $work): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
            'consent' => ['accepted'],
        ]);

        $deviceToken = $this->resolveDeviceToken($request);

        if ($this->isVisitorBlocked($deviceToken, $request->ip())) {
            return back()->withErrors(['body' => __('reviews.review_blocked')]);
        }

        $work->reviews()->create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'rating' => $validated['rating'],
            'body' => $validated['body'],
            'status' => 'pending',
            'from_client' => true,
            'ip_address' => $request->ip(),
            'device_token' => $deviceToken,
        ]);

        $message = "تقييم عميل وصل من رابط الطلب\n".
            'المشروع: '.$work->title."\n".
            "من: {$validated['name']}\n".
            "التقييم: {$validated['rating']}/5\n".
            "النص: {$validated['body']}\n".
            'للاعتماد: '.route('admin.reviews.index');

        $this->notifyManagerBot('⭐ '.$message);
        WhatsAppAlerts::send($message, '⭐ ');

        return redirect()->to($request->fullUrl())
            ->with('review_sent', true)
            ->withCookie($this->deviceTokenCookie($deviceToken));
    }
}

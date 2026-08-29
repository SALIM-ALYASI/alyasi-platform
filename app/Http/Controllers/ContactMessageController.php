<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NotifiesManagerBot;
use App\Http\Controllers\Concerns\ProtectsAgainstAbuse;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContactMessageController extends Controller
{
    use NotifiesManagerBot;
    use ProtectsAgainstAbuse;

    /**
     * إرسال رسالة تواصل جديدة.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $deviceToken = $this->resolveDeviceToken($request);

        $redirect = redirect(localized_route('contact'))
            ->withFragment('contact-form');

        if ($this->isVisitorBlocked($deviceToken, $request->ip())) {
            return $redirect->withErrors([
                'message' => __('contact.form_blocked'),
            ]);
        }

        /*
         * فخ بوتات: حقل "website" لازم يضل فاضي (مخفي عن المستخدم الحقيقي)،
         * والنموذج لازم ياخذ ٣ ثواني على الأقل قبل الإرسال (البوتات تعبّي
         * وترسل فوراً). لو فشل أي شرط، نظهر نجاح وهمي بدون ما نحفظ ولا
         * نرسل إشعار - نتجنب تنبيه البوت إنه انكشف.
         */
        $isSuspectedBot = filled($request->input('website'))
            || (int) $request->input('form_rendered_at') > (now()->timestamp - 3);

        if ($isSuspectedBot) {
            return $redirect
                ->with('success', __('contact.form_submitted'))
                ->withCookie($this->deviceTokenCookie($deviceToken));
        }

        ContactMessage::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => 'new',
            'ip_address' => $request->ip(),
            'device_token' => $deviceToken,
        ]);

        $this->notifyManagerBot(
            "✉️ رسالة تواصل جديدة\n".
            "من: {$validated['name']}\n".
            "البريد: {$validated['email']}\n".
            ($validated['subject'] ?? null ? "الموضوع: {$validated['subject']}\n" : '').
            "النص: ".Str::limit($validated['message'], 300)
        );

        return $redirect
            ->with('success', __('contact.form_submitted'))
            ->withCookie($this->deviceTokenCookie($deviceToken));
    }
}

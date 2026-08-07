<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProtectsAgainstAbuse;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
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

        $redirect = redirect()
            ->route('contact')
            ->withFragment('contact-form');

        if ($this->isVisitorBlocked($deviceToken, $request->ip())) {
            return $redirect->withErrors([
                'message' => __('contact.form_blocked'),
            ]);
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

        return $redirect
            ->with('success', __('contact.form_submitted'))
            ->withCookie($this->deviceTokenCookie($deviceToken));
    }
}

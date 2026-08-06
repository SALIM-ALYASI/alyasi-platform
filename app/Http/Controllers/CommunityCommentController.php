<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProtectsAgainstAbuse;
use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommunityCommentController extends Controller
{
    use ProtectsAgainstAbuse;

    /**
     * إضافة تعليق جديد على منشور المجتمع (بانتظار المراجعة).
     */
    public function store(Request $request, CommunityPost $communityPost): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'followed_confirmation' => ['accepted'],
        ]);

        $deviceToken = $this->resolveDeviceToken($request);

        if ($this->isVisitorBlocked($deviceToken, $request->ip())) {
            return back()
                ->withErrors(['body' => __('community.comment_blocked')])
                ->withFragment('comments');
        }

        $communityPost->comments()->create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'body' => $validated['body'],
            'followed_confirmation' => true,
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'device_token' => $deviceToken,
        ]);

        return back()
            ->with('success', __('community.comment_submitted'))
            ->withFragment('comments')
            ->withCookie($this->deviceTokenCookie($deviceToken));
    }
}

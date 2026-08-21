<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class YoutubeAuthController extends Controller
{
    /**
     * صلاحيات نشر فيديوهات يوتيوب، وقراءة التعليقات والرد عليها وإدارتها.
     */
    private const SCOPE = 'https://www.googleapis.com/auth/youtube.force-ssl https://www.googleapis.com/auth/youtube.upload';

    /**
     * عرض حالة الربط مع يوتيوب.
     */
    public function index(): View
    {
        $configured = filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));

        $connected = filled(Setting::get('youtube_refresh_token'));

        return view('admin.youtube.index', [
            'configured' => $configured,
            'connected' => $connected,
            'channelTitle' => Setting::get('youtube_channel_title'),
            'connectedAt' => Setting::get('youtube_connected_at'),
            'scope' => Setting::get('youtube_scope'),
        ]);
    }

    /**
     * تحويل المستخدم إلى شاشة موافقة Google لطلب صلاحيات يوتيوب.
     */
    public function connect(Request $request): RedirectResponse
    {
        abort_unless(
            filled(config('services.google.client_id'))
                && filled(config('services.google.client_secret')),
            500,
            'إعدادات Google OAuth غير مكتملة. أضف GOOGLE_CLIENT_ID و GOOGLE_CLIENT_SECRET في ملف .env أولاً.'
        );

        $state = Str::random(40);

        $request->session()->put('youtube_oauth_state', $state);

        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => route('admin.youtube.callback'),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'scope' => self::SCOPE,
            'state' => $state,
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    /**
     * استقبال الرد من Google وتبادل الكود بتوكن وصول وتوكن تجديد.
     */
    public function callback(Request $request): RedirectResponse
    {
        $expectedState = $request->session()->pull('youtube_oauth_state');

        if ($request->query('error')) {
            return redirect()
                ->route('admin.youtube.index')
                ->with('error', 'تم إلغاء ربط يوتيوب: '.$request->query('error'));
        }

        if (! $expectedState || $expectedState !== $request->query('state')) {
            return redirect()
                ->route('admin.youtube.index')
                ->with('error', 'فشل التحقق من الطلب، حاول ربط الحساب مرة أخرى.');
        }

        $code = $request->query('code');

        if (! $code) {
            return redirect()
                ->route('admin.youtube.index')
                ->with('error', 'لم يتم استلام كود التفويض من Google.');
        }

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => route('admin.youtube.callback'),
            'grant_type' => 'authorization_code',
        ]);

        if ($tokenResponse->failed()) {
            return redirect()
                ->route('admin.youtube.index')
                ->with('error', 'فشل تبادل الكود مع Google: '.($tokenResponse->json('error_description') ?? $tokenResponse->body()));
        }

        $tokens = $tokenResponse->json();

        if (empty($tokens['refresh_token'])) {
            return redirect()
                ->route('admin.youtube.index')
                ->with('error', 'لم يصل refresh_token من Google. افصل وصول التطبيق من إعدادات حساب Google ثم أعد الربط.');
        }

        Setting::set('youtube_access_token', $tokens['access_token']);
        Setting::set('youtube_refresh_token', $tokens['refresh_token']);
        Setting::set('youtube_token_expires_at', now()->addSeconds((int) $tokens['expires_in'])->toDateTimeString());
        Setting::set('youtube_scope', $tokens['scope'] ?? self::SCOPE);
        Setting::set('youtube_connected_at', now()->toDateTimeString());

        $channelResponse = Http::withToken($tokens['access_token'])
            ->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet',
                'mine' => 'true',
            ]);

        $channelTitle = $channelResponse->json('items.0.snippet.title');

        Setting::set('youtube_channel_title', $channelTitle);

        return redirect()
            ->route('admin.youtube.index')
            ->with('success', 'تم ربط قناة يوتيوب بنجاح.')
            ->with('youtube_new_refresh_token', $tokens['refresh_token'])
            ->with('youtube_new_access_token', $tokens['access_token']);
    }

    /**
     * فصل الربط وحذف التوكنات المخزّنة.
     */
    public function disconnect(): RedirectResponse
    {
        foreach ([
            'youtube_access_token',
            'youtube_refresh_token',
            'youtube_token_expires_at',
            'youtube_scope',
            'youtube_connected_at',
            'youtube_channel_title',
        ] as $key) {
            Setting::set($key, null);
        }

        return redirect()
            ->route('admin.youtube.index')
            ->with('success', 'تم فصل ربط قناة يوتيوب.');
    }
}

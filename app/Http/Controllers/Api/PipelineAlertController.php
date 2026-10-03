<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\NotifiesWhatsApp;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * تنبيه فشل خط فيديو الأخبار بـn8n (Approved News Media Pipeline) -- يوصل
 * عبر الجسر المجاني (whatsapp_notify). n8n ما عنده مفتاح الجسر، فينادي
 * هنا بنفس سر X-Internal-Secret المشترك، وLaravel يرسل.
 *
 * نفس الخطأ بنفس الخطوة ما يتكرر خلال 30 دقيقة -- عطل واحد (مثل SoundInk
 * طايح) يفشّل كل أخبار الدورة مع بعض، فرسالة وحدة تكفي بدل سيل رسائل.
 */
class PipelineAlertController extends Controller
{
    use NotifiesWhatsApp;

    private const THROTTLE_MINUTES = 30;

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'workflow' => ['required', 'string', 'max:200'],
            'node' => ['nullable', 'string', 'max:200'],
            'error' => ['required', 'string', 'max:2000'],
            'title' => ['nullable', 'string', 'max:500'],
            'execution_url' => ['nullable', 'url', 'max:500'],
        ]);

        $throttleKey = 'pipeline-alert:'.md5($data['workflow'].'|'.($data['node'] ?? '').'|'.$data['error']);

        if (! Cache::add($throttleKey, true, now()->addMinutes(self::THROTTLE_MINUTES))) {
            return response()->json(['success' => true, 'throttled' => true]);
        }

        $lines = [
            '🚨 فشل فيديو خبر',
            'المسار: '.$data['workflow'],
        ];

        if (filled($data['node'] ?? null)) {
            $lines[] = 'الخطوة: '.$data['node'];
        }

        if (filled($data['title'] ?? null)) {
            $lines[] = 'الخبر: '.$data['title'];
        }

        $lines[] = 'الخطأ: '.Str::limit($data['error'], 300);

        if (filled($data['execution_url'] ?? null)) {
            $lines[] = '';
            $lines[] = $data['execution_url'];
        }

        $this->notifyWhatsApp(implode("\n", $lines));

        return response()->json(['success' => true, 'throttled' => false]);
    }
}

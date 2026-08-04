<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request)
    {
        if ($request->get('hub_mode') === 'subscribe'
            && $request->get('hub_verify_token') === config('services.whatsapp_cloud.webhook_verify_token')) {
            return response($request->get('hub_challenge'), 200, ['Content-Type' => 'text/plain']);
        }

        return response('Forbidden', 403);
    }

    public function receive(Request $request)
    {
        Log::info('WhatsApp webhook event', $request->all());

        return response()->json(['success' => true]);
    }
}

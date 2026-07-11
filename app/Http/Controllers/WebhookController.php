<?php

namespace App\Http\Controllers;

use App\Services\BillingGatewayFactory;
use App\Services\CajuPayBillingService;
use App\Services\WooviBillingService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function cajupay(Request $request, CajuPayBillingService $billing)
    {
        $billing->handleWebhook($request->all());
        return response()->json(['ok' => true]);
    }

    public function woovi(Request $request, WooviBillingService $billing)
    {
        $signature = $request->header('x-webhook-secret', '');
        $rawPayload = $request->getContent();

        if (!$billing->verifyWebhookSignature($rawPayload, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $billing->handleWebhook($request->all());
        return response()->json(['ok' => true]);
    }
}

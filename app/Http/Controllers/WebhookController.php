<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function nowpayments(Request $request)
    {
        $sig = $request->header('x-nowpayments-sig');
        $ipnSecret = config('services.nowpayments.ipn_secret');

        if ($ipnSecret) {
            $payload = $request->getContent();
            $expectedSig = hash_hmac('sha512', $payload, $ipnSecret);

            if (!hash_equals($expectedSig, (string) $sig)) {
                Log::warning('NOWPayments webhook: invalid signature');
                return response('Forbidden', 403);
            }
        }

        $status = $request->input('payment_status');

        if (!in_array($status, ['finished', 'confirmed'])) {
            return response('OK', 200);
        }

        $orderId = $request->input('order_id', '');
        $parts   = explode('_', $orderId);

        if (count($parts) < 2 || $parts[0] !== 'user') {
            Log::warning('NOWPayments webhook: invalid order_id: ' . $orderId);
            return response('OK', 200);
        }

        $userId    = (int) $parts[1];
        $amount    = (float) $request->input('price_amount', 0);
        $paymentId = (string) $request->input('payment_id', '');

        if ($amount <= 0) {
            return response('OK', 200);
        }

        $user = User::find($userId);

        if (!$user) {
            Log::warning('NOWPayments webhook: user not found: ' . $userId);
            return response('OK', 200);
        }

        $alreadyProcessed = $user->transactions()
            ->where('reference', $paymentId)
            ->exists();

        if ($alreadyProcessed) {
            return response('OK', 200);
        }

        app(CreditService::class)->deposit($user, $amount, $paymentId);

        Log::info("NOWPayments: deposited {$amount} credits to user {$userId} (payment {$paymentId})");

        return response('OK', 200);
    }
}

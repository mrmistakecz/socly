<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Transaction;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $transactions = Transaction::where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('Wallet', [
            'balance'      => (float) $user->balance,
            'cryptoWallet' => $user->crypto_wallet,
            'transactions' => $transactions,
        ]);
    }

    public function createDeposit(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'integer', 'min:50', 'max:50000'],
        ]);

        $response = Http::withHeaders([
            'x-api-key' => config('services.nowpayments.api_key'),
        ])->post('https://api.nowpayments.io/v1/invoice', [
            'price_amount'       => $request->amount,
            'price_currency'     => 'czk',
            'order_id'           => 'user_' . Auth::id() . '_' . time(),
            'order_description'  => $request->amount . ' kreditů pro SOCLY',
            'ipn_callback_url'   => config('app.url') . '/webhook/nowpayments',
            'success_url'        => config('app.url') . '/wallet?success=1',
            'cancel_url'         => config('app.url') . '/wallet?cancel=1',
        ]);

        if (!$response->successful() || empty($response['invoice_url'])) {
            return response()->json(['error' => 'Nepodařilo se vytvořit platbu. Zkuste to znovu.'], 502);
        }

        return response()->json(['invoice_url' => $response['invoice_url']]);
    }

    public function unlockPost(Request $request, Post $post)
    {
        $user = Auth::user();

        if (!$post->is_locked) {
            return response()->json(['error' => 'Příspěvek není zamčený.'], 400);
        }

        if ($user->isSubscribedTo($post->user)) {
            return response()->json(['unlocked' => true]);
        }

        $alreadyUnlocked = Transaction::where('user_id', $user->id)
            ->where('type', 'unlock')
            ->where('reference', 'post_' . $post->id)
            ->exists();

        if ($alreadyUnlocked) {
            return response()->json(['unlocked' => true]);
        }

        $price = $post->price ?? 50;

        try {
            app(CreditService::class)->spend($user, $price, 'unlock', 'post_' . $post->id);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 402);
        }

        $creatorShare = round($price * 0.80, 2);
        app(CreditService::class)->deposit($post->user, $creatorShare, 'sale_post_' . $post->id);

        $user->refresh();

        return response()->json([
            'unlocked' => true,
            'balance'  => (float) $user->balance,
        ]);
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount'        => ['required', 'integer', 'min:100'],
            'crypto_wallet' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        if ($request->filled('crypto_wallet')) {
            $user->crypto_wallet = $request->crypto_wallet;
            $user->save();
        }

        if (empty($user->crypto_wallet)) {
            return response()->json(['error' => 'Nastav krypto peněženku v profilu.'], 422);
        }

        try {
            app(CreditService::class)->withdraw($user, $request->amount);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 402);
        }

        return response()->json([
            'success' => true,
            'balance' => (float) $user->fresh()->balance,
        ]);
    }

    public function updateWallet(Request $request)
    {
        $request->validate([
            'crypto_wallet' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->update(['crypto_wallet' => $request->crypto_wallet]);

        return back()->with('success', 'Krypto peněženka uložena.');
    }
}

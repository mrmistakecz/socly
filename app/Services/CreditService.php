<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function deposit(User $user, float $amount, string $reference): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $reference) {
            $user->increment('balance', $amount);
            $user->refresh();

            return Transaction::create([
                'user_id'      => $user->id,
                'type'         => 'deposit',
                'amount'       => $amount,
                'balance_after' => $user->balance,
                'reference'    => $reference,
                'status'       => 'completed',
            ]);
        });
    }

    public function spend(User $user, float $amount, string $type, string $reference): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $reference) {
            if ((float) $user->balance < $amount) {
                throw new \Exception('Nedostatek kreditů.');
            }

            $user->decrement('balance', $amount);
            $user->refresh();

            return Transaction::create([
                'user_id'      => $user->id,
                'type'         => $type,
                'amount'       => -$amount,
                'balance_after' => $user->balance,
                'reference'    => $reference,
                'status'       => 'completed',
            ]);
        });
    }

    public function withdraw(User $user, float $amount): Transaction
    {
        return DB::transaction(function () use ($user, $amount) {
            if ((float) $user->balance < $amount) {
                throw new \Exception('Nedostatek kreditů.');
            }

            $user->decrement('balance', $amount);
            $user->refresh();

            return Transaction::create([
                'user_id'      => $user->id,
                'type'         => 'withdrawal',
                'amount'       => -$amount,
                'balance_after' => $user->balance,
                'reference'    => null,
                'status'       => 'pending',
            ]);
        });
    }
}

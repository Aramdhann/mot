<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Wallet extends Model
{
    protected $fillable = ['name', 'type'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Balance per wallet, derived from transactions — never stored, never drifts.
     * ponytail: 3 aggregate queries + per-request memo; fine for personal use, materialized balance column if it ever lags.
     *
     * @return array<int, float>  wallet_id => balance
     */
    public static function balancesById(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $totals = [];

        $rows = Transaction::query()
            ->whereNot('type', 'transfer')
            ->whereNotNull('wallet_id')
            ->groupBy('wallet_id')
            ->selectRaw('wallet_id, sum(case when type = ? then amount else -amount end) as total', ['income'])
            ->pluck('total', 'wallet_id');
        foreach ($rows as $walletId => $total) {
            $totals[$walletId] = (float) $total;
        }

        $out = Transaction::query()
            ->where('type', 'transfer')
            ->whereNotNull('wallet_id')
            ->groupBy('wallet_id')
            ->selectRaw('wallet_id, sum(amount) as total')
            ->pluck('total', 'wallet_id');
        foreach ($out as $walletId => $total) {
            $totals[$walletId] = ($totals[$walletId] ?? 0) - (float) $total;
        }

        $in = Transaction::query()
            ->where('type', 'transfer')
            ->whereNotNull('transfer_to_wallet_id')
            ->groupBy('transfer_to_wallet_id')
            ->selectRaw('transfer_to_wallet_id as wallet_id, sum(amount) as total')
            ->pluck('total', 'wallet_id');
        foreach ($in as $walletId => $total) {
            $totals[$walletId] = ($totals[$walletId] ?? 0) + (float) $total;
        }

        return $cache = $totals;
    }

    public function getBalanceAttribute(): float
    {
        return static::balancesById()[$this->id] ?? 0.0;
    }
}

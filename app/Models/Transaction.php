<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'wallet_id', 'type', 'amount', 'category', 'description',
        'occurred_on', 'transfer_to_wallet_id', 'loan_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_on' => 'date',
            'type' => TransactionType::class,
        ];
    }

    /**
     * Normalize fields per type in ONE place, so every entry path stays consistent.
     */
    protected static function booted(): void
    {
        static::saving(function (self $transaction) {
            if ($transaction->type === TransactionType::Transfer) {
                $transaction->category = null;
                $transaction->loan_id = null;
            } elseif ($transaction->type === TransactionType::LoanPayment) {
                $transaction->category = 'loan';
                $transaction->transfer_to_wallet_id = null;
            } else {
                $transaction->transfer_to_wallet_id = null;
                $transaction->loan_id = null;
            }
        });
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transferToWallet()
    {
        return $this->belongsTo(Wallet::class, 'transfer_to_wallet_id');
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}

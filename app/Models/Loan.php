<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use BelongsToUser;

    protected $fillable = ['name', 'principal', 'started_on', 'note'];

    protected function casts(): array
    {
        return [
            'principal' => 'decimal:2',
            'started_on' => 'date',
        ];
    }

    public function payments()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getPaidAttribute(): float
    {
        return (float) $this->payments()->where('type', 'loan_payment')->sum('amount');
    }

    public function getRemainingAttribute(): float
    {
        return (float) $this->principal - $this->paid;
    }
}

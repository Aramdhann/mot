<?php

namespace App\Rules;

use App\Support\Money;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Accepts a positive amount, or a math expression that evaluates to one ("15000*2+5000").
 * Null/empty pass through so optional fields stay optional.
 */
class MoneyExpression implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $evaluated = Money::evaluate((string) $value);

        if (! is_numeric($evaluated) || (float) $evaluated <= 0) {
            $fail('The :attribute must be a positive amount, or a math expression like 15000*2+5000.');
        }
    }
}

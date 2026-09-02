<?php

namespace App\Models\Concerns;

use App\Models\User;

/**
 * Per-user data isolation: every query is scoped to the authenticated user,
 * and user_id is auto-assigned on create. Applied to wallets, transactions,
 * budgets and loans — nothing else in the app needs to filter manually.
 */
trait BelongsToUser
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function bootBelongsToUser(): void
    {
        static::addGlobalScope(
            'user',
            fn ($query) => $query->where($query->getModel()->getTable().'.user_id', auth()->id())
        );

        static::creating(fn ($model) => $model->user_id ??= auth()->id());
    }
}

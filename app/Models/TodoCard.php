<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TodoCard extends Model
{
    use BelongsToUser;

    protected $fillable = ['title'];

    public function items(): HasMany
    {
        return $this->hasMany(TodoItem::class)->orderBy('sort');
    }
}

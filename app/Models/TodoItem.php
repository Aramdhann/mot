<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoItem extends Model
{
    protected $fillable = ['todo_card_id', 'content', 'is_done', 'sort'];

    protected function casts(): array
    {
        return ['is_done' => 'boolean'];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(TodoCard::class, 'todo_card_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkItem extends Model
{
    protected $fillable = ['link_card_id', 'title', 'url', 'description', 'price', 'sort'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(LinkCard::class, 'link_card_id');
    }
}

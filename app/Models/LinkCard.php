<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinkCard extends Model
{
    use BelongsToUser;

    protected $fillable = ['title', 'description'];

    public function items(): HasMany
    {
        return $this->hasMany(LinkItem::class)->orderBy('sort');
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use BelongsToUser;

    protected $fillable = ['title', 'content'];

    /**
     * Relevance-ranked full-text search over title + content, with a substring
     * fallback when full-text finds nothing (FTS matches whole words, 3+ chars,
     * ignores stopwords — partial words like "kuber" → "kubernetes" need LIKE).
     * ponytail: no typo tolerance — swap for Meilisearch/Scout when we move off shared hosting.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);

        if ($term === '') {
            return $query;
        }

        $fts = fn (Builder $q) => $q->whereRaw('MATCH(notes.title, notes.content) AGAINST(? IN NATURAL LANGUAGE MODE)', [$term]);

        // ponytail: one extra exists() query per search — fine at personal scale
        if (mb_strlen($term) >= 3 && (clone $query)->where($fts)->exists()) {
            return $query
                ->selectRaw('notes.*, MATCH(notes.title, notes.content) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$term])
                ->whereRaw('MATCH(notes.title, notes.content) AGAINST(? IN NATURAL LANGUAGE MODE)', [$term])
                ->reorder('relevance', 'desc');
        }

        // Any word as substring: catches partial words FTS can't see
        return $query->where(function (Builder $q) use ($term) {
            foreach (preg_split('/\s+/', $term) as $word) {
                $q->orWhere(
                    fn (Builder $w) => $w->where('title', 'like', "%{$word}%")
                        ->orWhere('content', 'like', "%{$word}%")
                );
            }
        });
    }
}

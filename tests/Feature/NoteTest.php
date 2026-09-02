<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\TestCase;

class NoteTest extends TestCase
{
    // ponytail: RefreshDatabase wraps tests in a transaction, and InnoDB FULLTEXT
    // can't see uncommitted rows — truncation commits instead, FTS works.
    use DatabaseTruncation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_search_finds_notes_by_title_and_content(): void
    {
        Note::create(['title' => 'Groceries', 'content' => "buy spinach\n- milk"]);
        Note::create(['title' => 'Server stuff', 'content' => 'nginx config for deployment']);
        Note::create(['title' => 'Unrelated', 'content' => 'nothing here']);

        $this->assertCount(1, Note::search('groceries')->get());
        $this->assertCount(1, Note::search('nginx')->get());
        $this->assertCount(0, Note::search('zebra')->get());
    }

    public function test_short_terms_fall_back_to_like(): void
    {
        Note::create(['title' => 'DB', 'content' => 'backup routine']);

        $this->assertCount(1, Note::search('db')->get());
        $this->assertCount(1, Note::search('backup')->get());
    }

    public function test_partial_words_match_via_substring_fallback(): void
    {
        Note::create(['title' => 'Kubernetes notes', 'content' => 'cluster basics']);

        $this->assertCount(1, Note::search('kuber')->get());              // partial word — FTS would miss
        $this->assertCount(1, Note::search('kubernetes cluster')->get()); // multi-word, any word
    }

    public function test_misspelled_words_do_not_match(): void
    {
        Note::create(['title' => 'Kubernetes notes', 'content' => 'cluster basics']);

        $this->assertCount(0, Note::search('kubernets')->get()); // typo ceiling — Meilisearch fixes this
    }
}

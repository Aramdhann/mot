<?php

namespace Tests\Feature;

use App\Models\TodoCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_card_keeps_items_with_order_and_done_flag(): void
    {
        $card = TodoCard::create(['title' => 'Groceries']);
        $card->items()->createMany([
            ['content' => 'milk', 'sort' => 1],
            ['content' => 'spinach', 'sort' => 2, 'is_done' => true],
        ]);

        $card = $card->fresh();
        $this->assertSame(['milk', 'spinach'], $card->items->pluck('content')->all());
        $this->assertSame(1, $card->items->where('is_done')->count());
    }

    public function test_search_matches_card_title_and_item_content(): void
    {
        $card = TodoCard::create(['title' => 'Groceries']);
        $card->items()->create(['content' => 'buy oat milk', 'sort' => 1]);
        TodoCard::create(['title' => 'Work stuff']);

        $query = fn (string $s) => TodoCard::query()
            ->where(fn ($q) => $q->where('todo_cards.title', 'like', "%{$s}%")
                ->orWhereHas('items', fn ($i) => $i->where('content', 'like', "%{$s}%")));

        $this->assertSame(['Groceries'], $query('grocer')->pluck('title')->all()); // card title
        $this->assertSame(['Groceries'], $query('oat milk')->pluck('title')->all()); // item content
        $this->assertCount(0, $query('zebra')->get());
    }

    public function test_deleting_card_cascades_its_items(): void
    {
        $card = TodoCard::create(['title' => 'Groceries']);
        $card->items()->create(['content' => 'milk', 'sort' => 1]);

        $card->delete();

        $this->assertDatabaseCount('todo_items', 0);
    }
}

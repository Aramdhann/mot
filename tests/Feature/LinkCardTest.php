<?php

namespace Tests\Feature;

use App\Models\LinkCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_card_keeps_links_with_order_and_price(): void
    {
        $card = LinkCard::create(['title' => 'Kipas angin']);
        $card->items()->createMany([
            ['title' => 'Miyako', 'url' => 'https://shop.example/miyako', 'price' => 349000, 'sort' => 1],
            ['title' => 'Cosmos', 'url' => 'https://shop.example/cosmos', 'sort' => 2],
        ]);

        $card = $card->fresh();
        $this->assertSame(['Miyako', 'Cosmos'], $card->items->pluck('title')->all());
        $this->assertSame('349000.00', $card->items->first()->price);
    }

    public function test_search_matches_card_brand_and_url(): void
    {
        $card = LinkCard::create(['title' => 'Kipas angin']);
        $card->items()->create(['title' => 'Miyako', 'url' => 'https://tokopedia.com/miyako-fan', 'sort' => 1]);
        LinkCard::create(['title' => 'Unrelated']);

        $query = fn (string $s) => LinkCard::query()
            ->where(fn ($q) => $q->where('link_cards.title', 'like', "%{$s}%")
                ->orWhere('link_cards.description', 'like', "%{$s}%")
                ->orWhereHas('items', fn ($i) => $i
                    ->where('title', 'like', "%{$s}%")
                    ->orWhere('url', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")));

        $this->assertSame(['Kipas angin'], $query('kipas')->pluck('title')->all()); // card title
        $this->assertSame(['Kipas angin'], $query('miyako')->pluck('title')->all()); // brand
        $this->assertSame(['Kipas angin'], $query('tokopedia')->pluck('title')->all()); // url
        $this->assertCount(0, $query('zebra')->get());
    }

    public function test_deleting_card_cascades_its_links(): void
    {
        $card = LinkCard::create(['title' => 'Kipas angin']);
        $card->items()->create(['title' => 'Miyako', 'url' => 'https://shop.example/miyako', 'sort' => 1]);

        $card->delete();

        $this->assertDatabaseCount('link_items', 0);
    }
}

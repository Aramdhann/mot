<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render(): void
    {
        $user = User::factory()->create();

        foreach (['/admin', '/admin/wallets', '/admin/transactions', '/admin/budgets', '/admin/loans'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_dashboard_renders_with_data(): void
    {
        $this->actingAs(User::factory()->create());

        $wallet = \App\Models\Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        $loan = \App\Models\Loan::create(['name' => 'Motor', 'principal' => 14000000, 'started_on' => now()]);
        \App\Models\Budget::create(['category' => 'makan', 'amount' => 2000000]);
        \App\Models\Transaction::create(['wallet_id' => $wallet->id, 'type' => 'expense', 'amount' => 75000, 'category' => 'makan', 'occurred_on' => now()]);
        \App\Models\Transaction::create(['wallet_id' => $wallet->id, 'type' => 'loan_payment', 'amount' => 1000000, 'loan_id' => $loan->id, 'occurred_on' => now()]);

        $this->get('/admin')->assertOk();
    }

    public function test_daily_spending_list_widget_mounts(): void
    {
        $this->actingAs(User::factory()->create());

        $wallet = \App\Models\Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        \App\Models\Transaction::create(['wallet_id' => $wallet->id, 'type' => 'expense', 'amount' => 75000, 'category' => 'makan', 'occurred_on' => now()]);

        \Livewire\Livewire::test(\App\Filament\Widgets\DailySpendingList::class)
            ->assertSuccessful()
            ->assertSeeText(number_format(75000, 0))
            ->assertSeeText(now()->format('D, d M Y'));
    }

    public function test_quick_create_menu_is_on_every_panel_page(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['/admin', '/admin/wallets', '/admin/transactions'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Quick create')
                ->assertSee('+ Transaction')
                ->assertSee('+ Wallet')
                ->assertSee('+ Budget')
                ->assertSee('+ Loan');
        }
    }

    public function test_action_query_param_mounts_create_form(): void
    {
        $this->actingAs(User::factory()->create());

        // the URL the quick-create menu links to — opens the create modal on load
        $this->get('/admin/wallets?action=create')->assertOk();
    }

    public function test_transaction_form_renders(): void
    {
        $this->actingAs(User::factory()->create());

        \Livewire\Livewire::test(\App\Filament\Resources\Transactions\Pages\ManageTransactions::class)
            ->mountAction('create')
            ->assertSuccessful();
    }
}

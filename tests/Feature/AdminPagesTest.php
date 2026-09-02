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

    public function test_category_field_offers_budget_categories(): void
    {
        $this->actingAs(User::factory()->create());
        \App\Models\Budget::create(['category' => 'makan', 'amount' => 2000000]);
        \App\Models\Budget::create(['category' => 'tersier', 'amount' => 500000]);

        $options = \App\Filament\Resources\Transactions\TransactionResource::categoryOptions();

        $this->assertArrayHasKey('makan', $options);
        $this->assertArrayHasKey('tersier', $options);
    }

    public function test_category_options_include_used_non_budget_categories(): void
    {
        $this->actingAs(User::factory()->create());
        $wallet = \App\Models\Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        \App\Models\Transaction::create(['wallet_id' => $wallet->id, 'type' => 'income', 'amount' => 1000000, 'category' => 'gaji', 'occurred_on' => now()]);

        $this->assertArrayHasKey('gaji', \App\Filament\Resources\Transactions\TransactionResource::categoryOptions());
    }

    public function test_inline_created_category_persists_as_no_limit_budget_and_validates(): void
    {
        $this->actingAs(User::factory()->create());
        $wallet = \App\Models\Wallet::create(['name' => 'BCA', 'type' => 'bank']);

        // what createOptionUsing does when the user adds "jajan" inline:
        $category = \App\Models\Budget::firstOrCreate(['category' => 'jajan'], ['amount' => null])->category;

        $this->assertDatabaseHas('budgets', ['category' => 'jajan', 'amount' => null]);
        $this->assertArrayHasKey('jajan', \App\Filament\Resources\Transactions\TransactionResource::categoryOptions());

        // now the transaction form accepts it (the exact submit that failed for the user)
        \Livewire\Livewire::test(\App\Filament\Resources\Transactions\Pages\ManageTransactions::class)
            ->callAction('create', data: [
                'type' => 'expense',
                'wallet_id' => $wallet->id,
                'amount' => 25000,
                'category' => $category,
                'occurred_on' => now()->format('Y-m-d'),
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('transactions', ['category' => 'jajan', 'amount' => 25000]);
    }

    public function test_budgets_page_renders_with_no_limit_budget(): void
    {
        $this->actingAs(User::factory()->create());
        \App\Models\Budget::create(['category' => 'jajan']);

        $this->get('/admin/budgets')->assertOk()->assertSee('No limit');
    }

    public function test_budget_summary_flags_over_budget(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $wallet = \App\Models\Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        \App\Models\Transaction::create(['wallet_id' => $wallet->id, 'type' => 'income', 'amount' => 2500000, 'category' => 'gaji', 'occurred_on' => now()]);
        \App\Models\Budget::create(['category' => 'makan', 'amount' => 2000000]);
        \App\Models\Budget::create(['category' => 'tersier', 'amount' => 1000000]);
        // no-limit budget must not count toward the total
        \App\Models\Budget::create(['category' => 'jajan', 'amount' => null]);

        $summary = \App\Filament\Resources\Budgets\BudgetResource::budgetSummary();

        $this->assertStringContainsString('OVER', $summary);         // 3,000,000 budgeted vs 2,500,000 income
        $this->assertStringContainsString('500,000', $summary);

        $this->get('/admin/budgets')->assertOk()->assertSee('OVER');
    }

    public function test_budget_summary_under_budget(): void
    {
        $this->actingAs(User::factory()->create());
        $wallet = \App\Models\Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        \App\Models\Transaction::create(['wallet_id' => $wallet->id, 'type' => 'income', 'amount' => 2000000, 'category' => 'gaji', 'occurred_on' => now()]);
        \App\Models\Budget::create(['category' => 'makan', 'amount' => 1500000]);

        $this->assertStringContainsString('under', \App\Filament\Resources\Budgets\BudgetResource::budgetSummary());
    }

    public function test_dashboard_budget_widget_mounts_with_summary(): void
    {
        $this->actingAs(User::factory()->create());
        $wallet = \App\Models\Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        \App\Models\Transaction::create(['wallet_id' => $wallet->id, 'type' => 'income', 'amount' => 2000000, 'category' => 'gaji', 'occurred_on' => now()]);
        \App\Models\Budget::create(['category' => 'makan', 'amount' => 1500000]);

        \Livewire\Livewire::test(\App\Filament\Widgets\BudgetProgress::class)
            ->assertSuccessful()
            ->assertSeeText('under');
    }

    public function test_transaction_form_renders(): void
    {
        $this->actingAs(User::factory()->create());

        \Livewire\Livewire::test(\App\Filament\Resources\Transactions\Pages\ManageTransactions::class)
            ->mountAction('create')
            ->assertSuccessful();
    }
}

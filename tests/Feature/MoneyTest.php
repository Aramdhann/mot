<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluates_basic_math(): void
    {
        $this->assertSame('35000', Money::evaluate('15000*2+5000'));
        $this->assertSame('100000', Money::evaluate('(250000+50000)/3'));
        $this->assertSame('5000', Money::evaluate(' 10 000 - 5000 '));
    }

    public function test_passthrough_without_operator(): void
    {
        $this->assertSame('35000', Money::evaluate('35000'));
        $this->assertNull(Money::evaluate(null));
        $this->assertSame('', Money::evaluate(''));
    }

    public function test_dangerous_input_falls_through_untouched(): void
    {
        // never evaluated — falls to numeric validation which rejects it
        $this->assertSame('echo "x"', Money::evaluate('echo "x"'));
        $this->assertSame('1;phpinfo()', Money::evaluate('1;phpinfo()'));
        $this->assertSame('${destruct}', Money::evaluate('${destruct}'));
        $this->assertSame('5/0', Money::evaluate('5/0')); // div by zero -> passthrough
    }

    public function test_transaction_form_accepts_math_expression(): void
    {
        $this->actingAs(User::factory()->create());
        $wallet = \App\Models\Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        \App\Models\Budget::create(['category' => 'makan']);

        \Livewire\Livewire::test(\App\Filament\Resources\Transactions\Pages\ManageTransactions::class)
            ->callAction('create', data: [
                'type' => 'expense',
                'wallet_id' => $wallet->id,
                'amount' => '15000*2+5000',
                'category' => 'makan',
                'occurred_on' => now()->format('Y-m-d'),
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('transactions', ['amount' => 35000]);
    }

    public function test_calculator_keypad_renders_with_state_path(): void
    {
        $html = view('filament.calculator', ['statePath' => 'mountedActions.0.data.amount'])->render();

        $this->assertStringContainsString("mountedActions.0.data.amount", $html);
        $this->assertStringContainsString('Use', $html);
        $this->assertStringContainsString('unmountAction', $html);
    }

    public function test_calculator_suffix_action_mounts_on_amount_field(): void
    {
        $this->actingAs(User::factory()->create());
        \App\Models\Wallet::create(['name' => 'BCA', 'type' => 'bank']);

        $this->get('/admin/transactions?action=create')->assertOk();
    }
}

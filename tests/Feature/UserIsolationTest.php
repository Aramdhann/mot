<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_own_data(): void
    {
        $admin = User::factory()->create();
        $other = User::factory()->create();

        // admin's data
        $this->actingAs($admin);
        $wallet = Wallet::create(['name' => 'Admin BCA', 'type' => 'bank']);
        Transaction::create(['wallet_id' => $wallet->id, 'type' => 'income', 'amount' => 1000000, 'category' => 'gaji', 'occurred_on' => now()]);
        Budget::create(['category' => 'makan', 'amount' => 500000]);
        Loan::create(['name' => 'Admin Motor', 'principal' => 5000000, 'started_on' => now()]);

        // other user sees nothing
        $this->actingAs($other);
        $this->assertEquals(0, Wallet::count());
        $this->assertEquals(0, Transaction::count());
        $this->assertEquals(0, Budget::count());
        $this->assertEquals(0, Loan::count());
        $this->assertNull(Wallet::find($wallet->id));
        $this->assertEquals([], Wallet::balancesById());

        // other user's own data is visible to them
        $otherWallet = Wallet::create(['name' => 'Other Cash', 'type' => 'cash']);
        $this->assertEquals(1, Wallet::count());
        $this->assertTrue($otherWallet->fresh()->user->is($other));
    }

    public function test_cannot_access_other_users_wallet_in_form(): void
    {
        $admin = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($admin);
        $adminWallet = Wallet::create(['name' => 'Admin BCA', 'type' => 'bank']);

        // other user's transaction form: wallet dropdown only shows their own wallets
        $this->actingAs($other);
        $options = Wallet::orderBy('name')->pluck('id')->all();

        $this->assertNotContains($adminWallet->id, $options);
    }
}

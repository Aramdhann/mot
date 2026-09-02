<?php

use App\Models\Budget;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['wallets', 'transactions', 'budgets', 'loans'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            });
        }

        // backfill: existing rows belong to the first user (admin)
        if ($firstUser = DB::table('users')->orderBy('id')->value('id')) {
            foreach (['wallets', 'transactions', 'budgets', 'loans'] as $table) {
                DB::table($table)->whereNull('user_id')->update(['user_id' => $firstUser]);
            }
        }

        foreach (['wallets', 'transactions', 'budgets', 'loans'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('user_id')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['wallets', 'transactions', 'budgets', 'loans'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('user_id');
            });
        }
    }
};

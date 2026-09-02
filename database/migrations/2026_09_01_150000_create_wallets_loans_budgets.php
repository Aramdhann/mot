<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('cash'); // cash | bank | ewallet | other
            $table->timestamps();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('principal', 14, 2);
            $table->date('started_on');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('category')->unique(); // couples budgets to transaction categories by name
            $table->decimal('amount', 14, 2); // monthly limit
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('wallet_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->default('expense')->index(); // income | expense | transfer | loan_payment
            $table->foreignId('transfer_to_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2)->change();
            $table->string('description')->nullable()->change();
            $table->string('category')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wallet_id');
            $table->dropConstrainedForeignId('transfer_to_wallet_id');
            $table->dropConstrainedForeignId('loan_id');
            $table->dropColumn(['type']);
            $table->decimal('amount', 12, 2)->change();
        });
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('wallets');
    }
};

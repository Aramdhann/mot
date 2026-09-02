<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // null = no limit: budgets double as the category registry
        Schema::table('budgets', function (Blueprint $table) {
            $table->decimal('amount', 14, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->decimal('amount', 14, 2)->nullable(false)->default(0)->change();
        });
    }
};

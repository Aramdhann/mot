<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // the thing being compared, e.g. "Kipas angin"
            $table->text('description')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('link_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_card_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // brand / shop name
            $table->string('url', 2048);
            $table->text('description')->nullable();
            $table->decimal('price', 14, 2)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_items');
        Schema::dropIfExists('link_cards');
    }
};

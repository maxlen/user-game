<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('access_link_id')->nullable()->constrained('access_links')->nullOnDelete();
            $table->unsignedSmallInteger('number');
            $table->boolean('is_win');
            $table->decimal('amount', 8, 2);
            $table->timestamps();

            $table->index(['player_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spins');
    }
};

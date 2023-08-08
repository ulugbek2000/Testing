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
        try {
            if (!Schema::hasTable('user_wallets'))
                Schema::create('user_wallets', function (Blueprint $table) {
                    $table->increments('id');
                    $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
                    $table->decimal('balance', 10, 2);
                    $table->timestamps();
                    $table->softDeletes();
                });
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_wallets');
    }
};

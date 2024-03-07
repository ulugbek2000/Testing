<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TransactionMethod;
use App\Enums\TransactionStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            if (!Schema::hasTable('user_transactions'))
                Schema::create('user_transactions', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('wallet_id')->references('id')->on('user_wallets')->onDelete('cascade');
                    $table->decimal('amount', 10, 2);
                    $table->decimal('total_earnings', 10, 2)->nullable();
                    $table->string('description')->nullable();
                    $table->enum('method', TransactionMethod::getValues())->default(TransactionMethod::Cash);
                    $table->enum('status', TransactionStatus::getValues())->default(TransactionStatus::Pending);
                    $table->unsignedBigInteger('user_id');
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                    $table->timestamps();
                    $table->softDeletes();
                });
        } catch (\Throwable $th) {
            echo 'error';
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_transactions');
    }
};

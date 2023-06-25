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
                $table->increments('id');
                $table->foreignId('wallet_id')->references('id')->on('user_wallets')->onDelete('cascade');
                $table->decimal('amount',10,2);
                $table->string('description')->nullable();
                // $table->enum('method', TransactionMethod::getValues())->default(TransactionMethod::CASH());
                $table->enum('method', ['cash', 'mobile', 'online'])->default('Cash');
                // $table->enum('status', TransactionStatus::getValues())->default(TransactionStatus::PENDING());
                $table->enum('status', ['pending', 'success', 'fail','processing'])->default('Pending');
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

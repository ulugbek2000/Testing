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
        // if (!Schema::hasTable('users'))
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('surname')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('gender')->nullable();
                $table->string('city')->nullable();
                $table->string('description')->nullable();
                $table->string('position')->nullable();
                $table->string('user_type')->nullable();
                // $table->foreignId('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->timestamp('email_verified_at')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('photo')->nullable();
                $table->timestamp('phone_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->text('two_factor_secret')->nullable();
                $table->text('two_factor_recovery_codes')->nullable();
                $table->timestamp('two_factor_confirmed_at')->nullable();
                $table->string('remember_token', '100')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

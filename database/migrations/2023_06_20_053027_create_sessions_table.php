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
            if (!Schema::hasTable('sessions'))
                Schema::create('sessions', function (Blueprint $table) {
                    $table->increments('id');
                    $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
                    $table->string('ip_address', '45')->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload')->nullable();
                    $table->integer('last_activity');
                    $table->timestamps();
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
        Schema::dropIfExists('sessions');
    }
};

<?php

use App\Enums\TransactionType;
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
            if (!Schema::hasTable('lessons'))
                Schema::create('lessons', function (Blueprint $table) {
                    $table->increments('id');
                    $table->foreignId('topic_id')->references('id')->on('topics')->onDelete('cascade');
                    $table->string('name');
                    $table->integer('duration');
                    // $table->enum('type', TransactionType::getValues())->default(TransactionType::CASH());
                    $table->enum('type', ['doc', 'video', 'audio', 'text', 'image', 'quiz']);
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
        Schema::dropIfExists('lessons');
    }
};

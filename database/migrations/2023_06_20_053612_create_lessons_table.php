<?php

use App\Enums\LessonType;
use App\Enums\LessonTypes;
use App\Enums\TransactionType;
use Illuminate\Database\Migrations\Migration;
use Spatie\Enum\Enum;
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
                    $table->id();
                    $table->foreignId('topic_id')->references('id')->on('topics')->onDelete('cascade');
                    $table->string('name');
                    $table->text('content');
                    $table->string('cover')->nullable();
                    $table->enum('type',LessonTypes::getValues());
                    $table->timestamps();
                    $table->softDeletes();
                });
        } catch (\Exception $e) {
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

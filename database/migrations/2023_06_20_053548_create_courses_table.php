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
       
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('logo')->nullable();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('quantity_lessons')->nullable();
                $table->string('hours_lessons')->nullable();
                $table->text('short_description');
                $table->string('video')->nullable();
                $table->boolean('has_certificate')->default(false);
                $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

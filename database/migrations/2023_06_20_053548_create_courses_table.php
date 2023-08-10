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
        if (!Schema::hasTable('courses'))
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('logo');
                $table->string('name');
                $table->string('slug');
                $table->string('quantity_lessons');
                $table->string('hours_lessons');
                $table->text('description');
                $table->string('video');
                $table->decimal('price', 10, 2);
                $table->integer('duration');
                $table->string('duration_type');
                $table->boolean('has_certificate')->default(false);
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

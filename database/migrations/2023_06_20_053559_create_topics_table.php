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
            if (!Schema::hasTable('topics')) 
            Schema::create('topics', function (Blueprint $table) {
                $table->increments('id');  
                $table->foreignId('course_id')->references('id')->on('courses')->onDelete('cascade');
                $table->string('name')->nullable();
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
        Schema::dropIfExists('topics');
    }
};

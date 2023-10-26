<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedBigInteger('latest_subscription_id')->nullable();
            $table->foreign('latest_subscription_id')->references('id')->on('subscriptions');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['latest_subscription_id']);
            $table->dropColumn('latest_subscription_id');
        });
    }
};

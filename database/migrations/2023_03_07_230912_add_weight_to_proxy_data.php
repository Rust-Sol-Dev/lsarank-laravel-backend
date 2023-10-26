<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proxy_data', function (Blueprint $table) {
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('as')->nullable();
            $table->string('subnet')->nullable();
            $table->bigInteger('weight')->default(1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proxy_data', function (Blueprint $table) {
            //
        });
    }
};

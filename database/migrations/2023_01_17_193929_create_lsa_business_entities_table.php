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
        Schema::create('lsa_business_entities', function (Blueprint $table) {
            $table->id();
            $table->text('profile_url_path');
            $table->string('customer_id');
            $table->string('name');
            $table->string('slug');
            $table->string('occupation');
            $table->string('phone');
            $table->string('keyword');
            $table->integer('lsa_ranking');
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->bigInteger('keyword_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('lsa_keyword')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lsa_business_entities');
    }
};

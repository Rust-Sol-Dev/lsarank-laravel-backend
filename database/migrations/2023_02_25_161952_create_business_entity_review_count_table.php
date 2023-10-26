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
        Schema::create('business_entity_review_count', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->bigInteger('keyword_id')->nullable()->unsigned();
            $table->bigInteger('business_entity_id')->nullable()->unsigned();
            $table->integer('review_count')->unsigned();
            $table->string('date')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('lsa_keyword')->onDelete('cascade');
            $table->foreign('business_entity_id')->references('id')->on('lsa_business_entities')->onDelete('cascade');
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
        Schema::dropIfExists('business_entity_review_count');
    }
};

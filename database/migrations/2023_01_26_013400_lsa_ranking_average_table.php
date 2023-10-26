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
        Schema::create('lsa_ranking_average', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('keyword_id')->nullable()->unsigned();
            $table->bigInteger('business_entity_id')->nullable()->unsigned();
            $table->foreign('business_entity_id')->references('id')->on('lsa_business_entities')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('lsa_business_entities')->onDelete('cascade');
            $table->date('date');
            $table->integer('rank_avg');
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
        Schema::dropIfExists('user_business_entity_preference');
    }
};

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
        Schema::create('business_entities_zipcode_rankings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('heat_map_id')->unsigned()->nullable();
            $table->bigInteger('business_entity_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('zipcode')->nullable();
            $table->integer('lsa_rank')->unsigned()->nullable();
            $table->string('keyword')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('business_entity_id')->references('id')->on('lsa_business_entities')->onDelete('cascade');
            $table->foreign('heat_map_id')->references('id')->on('business_entity_heat_map')->onDelete('cascade');
            $table->index(['heat_map_id', 'business_entity_id', 'user_id'], 'three_prop_id_index');
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
        //
    }
};

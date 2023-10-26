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
        Schema::table('business_entities_zipcode_rankings', function (Blueprint $table) {
            $table->string('batch_id');
            $table->dropForeign('business_entities_zipcode_rankings_business_entity_id_foreign');
            $table->dropForeign('business_entities_zipcode_rankings_heat_map_id_foreign');
            $table->dropForeign('business_entities_zipcode_rankings_user_id_foreign');
            $table->dropIndex('three_prop_id_index');
            $table->dropIndex('business_entities_zipcode_rankings_business_entity_id_foreign');
            $table->dropIndex('business_entities_zipcode_rankings_user_id_foreign');
            $table->index(['user_id', 'business_entity_id', 'heat_map_id', 'batch_id'], 'four_index');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('business_entity_id')->references('id')->on('lsa_business_entities')->onDelete('cascade');
            $table->foreign('heat_map_id')->references('id')->on('business_entity_heat_map')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zipcode_rankings', function (Blueprint $table) {
            //
        });
    }
};

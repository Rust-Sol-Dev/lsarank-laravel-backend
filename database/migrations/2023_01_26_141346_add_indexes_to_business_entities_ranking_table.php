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
        Schema::table('business_entities_ranking', function (Blueprint $table) {
            $table->integer('lsa_rank')->index()->change();
            $table->string('day')->index()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_entities_ranking', function (Blueprint $table) {
            //
        });
    }
};

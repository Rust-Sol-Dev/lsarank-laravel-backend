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
        Schema::table('lsa_ranking_average', function (Blueprint $table) {
            $table->date('date')->index()->change();
            $table->integer('rank_avg')->index()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lsa_ranking_average', function (Blueprint $table) {
            $table->dropIndex('date');
            $table->dropIndex('rank_avg');
        });
    }
};

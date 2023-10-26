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
        Schema::create('lsa_ranking_average_weekly', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('keyword_id')->nullable()->unsigned();
            $table->bigInteger('business_entity_id')->nullable()->unsigned();
            $table->timestamp('week_start');
            $table->timestamp('week_end');
            $table->timestamp('current_date');
            $table->decimal('rank_avg');
            $table->index(['keyword_id', 'business_entity_id']);
            $table->index(['week_start', 'week_end']);
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

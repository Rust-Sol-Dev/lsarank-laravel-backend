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
            $table->dropIndex('business_entities_ranking_business_entity_id_foreign');
            $table->dropIndex('business_entities_ranking_day_index');
            $table->dropIndex('business_entities_ranking_keyword_id_foreign');
            $table->dropIndex('business_entities_ranking_lsa_rank_index');
            $table->dropIndex('business_entities_ranking_user_id_foreign');
        });

        \Illuminate\Support\Facades\DB::statement('ALTER TABLE business_entities_ranking ADD INDEX business_ranking_idx_user_id_keyword_day_created (user_id,keyword_id,day,created_at desc);');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE business_entities_ranking ADD INDEX business_ranking_idx_created_at (created_at desc);');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE business_entities_ranking ADD INDEX business_entities__idx_user_id_keyword_day_created_2 (user_id,keyword_id,day,created_at);');

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

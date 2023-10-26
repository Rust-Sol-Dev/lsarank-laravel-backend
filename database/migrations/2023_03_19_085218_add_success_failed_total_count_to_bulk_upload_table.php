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
        Schema::table('keyword_bulk_upload', function (Blueprint $table) {
            $table->integer('failed_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('total_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bulk_upload', function (Blueprint $table) {
            //
        });
    }
};

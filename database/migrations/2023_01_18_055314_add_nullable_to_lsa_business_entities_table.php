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
        Schema::table('lsa_business_entities', function (Blueprint $table) {
            $table->text('profile_url_path')->nullable()->change();
            $table->string('customer_id')->nullable()->change();
            $table->string('name')->nullable()->change();
            $table->string('slug')->nullable()->change();
            $table->string('occupation')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('keyword')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lsa_business_entities', function (Blueprint $table) {
            //
        });
    }
};

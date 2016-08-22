<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdToProvisionalTcaTxs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provisional_tca_txs', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provisional_tca_txs', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}

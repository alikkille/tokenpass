<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNotesToProvisionalTcaTxs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provisional_tca_txs', function (Blueprint $table) {
            $table->string('note')->nullable();
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
            $table->dropColumn('note');
        });
    }
}

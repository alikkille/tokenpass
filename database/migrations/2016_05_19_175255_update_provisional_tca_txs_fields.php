<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateProvisionalTcaTxsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provisional_tca_txs', function (Blueprint $table) {
            $table->integer('expiration')->nullable();
            $table->string('ref')->nullable();
            $table->index('ref');
            $table->boolean('pseudo')->default(0);
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
            $table->dropColumn('expiration');
            $table->dropColumn('ref');
            $table->dropColumn('pseudo');
        });
    }
}

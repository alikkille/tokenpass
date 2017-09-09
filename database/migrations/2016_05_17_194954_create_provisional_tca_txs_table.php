<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProvisionalTcaTxsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provisional_tca_txs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source');
            $table->string('destination');
            $table->index('source');
            $table->index('destination');
            $table->string('asset');
            $table->bigInteger('quantity');
            $table->string('fingerprint')->nullable();
            $table->index('fingerprint');
            $table->string('txid')->nullable();
            $table->index('txid');
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
        Schema::drop('provisional_tca_txs');
    }
}

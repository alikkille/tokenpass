<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('fingerprint');
            $table->index('fingerprint');
            $table->string('txid');
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

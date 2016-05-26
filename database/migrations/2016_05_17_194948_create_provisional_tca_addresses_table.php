<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvisionalTcaAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provisional_tca_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('address');
            $table->index('address');
            $table->text('proof');
            $table->string('client_id');
            $table->index('client_id');
            $table->text('assets')->nullable();
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
        Schema::drop('provisional_tca_addresses');
    }
}

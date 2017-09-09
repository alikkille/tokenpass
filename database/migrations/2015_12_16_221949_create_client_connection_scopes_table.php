<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClientConnectionScopesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_connection_scopes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('connection_id')->unsigned();
            $table->foreign('connection_id')
                  ->references('id')->on('client_connections')
                  ->onDelete('cascade');
            $table->string('scope_id');
            $table->foreign('scope_id')
                  ->references('uuid')->on('oauth_scopes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('client_connection_scopes');
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: one
 * Date: 30/06/16
 * Time: 20:45.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateClientConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_connections', function (Blueprint $table) {
            $table->dropForeign('client_connections_client_id_foreign');
            $table->foreign('client_id')
                ->references('id')->on('oauth_clients')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_connections', function (Blueprint $table) {
            $table->dropForeign('client_connections_client_id_foreign');
            $table->foreign('client_id')
                ->references('id')->on('oauth_clients')
                ->onUpdate('cascade');
        });
    }
}

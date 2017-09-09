<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMonitorIdsToAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_addresses', function (Blueprint $table) {
            $table->string('xchain_address_id', 36)->unique()->nullable();
            $table->string('receive_monitor_id', 36)->unique()->nullable();
            $table->string('send_monitor_id', 36)->unique()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coin_addresses', function (Blueprint $table) {
            $table->dropColumn('xchain_address_id');
            $table->dropColumn('receive_monitor_id');
            $table->dropColumn('send_monitor_id');
        });
    }
}

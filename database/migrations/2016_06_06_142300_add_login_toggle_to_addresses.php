<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddLoginToggleToAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_addresses', function (Blueprint $table) {
            $table->boolean('login_toggle')->default(0);
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
            $table->dropColumn('login_toggle');
        });
    }
}

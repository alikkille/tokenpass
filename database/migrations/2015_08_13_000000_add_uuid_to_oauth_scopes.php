<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUuidToOauthScopes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oauth_scopes', function (Blueprint $table) {
            $table->char('uuid', 36)->default('')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oauth_scopes', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
}

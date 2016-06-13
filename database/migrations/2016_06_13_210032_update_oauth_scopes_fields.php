<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOauthScopesFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oauth_scopes', function (Blueprint $table) {
            $table->string('label')->nullable();
            $table->integer('notice_level')->default(0);
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
            $table->dropColumn('label');
            $table->dropColumn('notice_level');
        });
    }
}

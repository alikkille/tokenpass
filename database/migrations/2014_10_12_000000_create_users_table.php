<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');

            $table->char('uuid', 36)->unique();
            $table->string('apitoken', 16)->unique();
            $table->string('apisecretkey', 40);

            $table->string('name')->nullable();
            $table->string('username')->unique();
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('password', 60);

            $table->text('privileges')->nullable();

            $table->string('confirmed_email')->nullable();
            $table->string('confirmation_code')->nullable()->unique();
            $table->timestamp('confirmation_code_expires_at')->nullable();

            $table->rememberToken();
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
        Schema::drop('users');
    }
}

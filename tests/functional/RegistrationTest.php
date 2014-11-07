<?php

use TKAccounts\TestHelpers\UserHelper;
use Illuminate\Support\Facades\App;

/*
* RegistrationTest
*/
class RegistrationTest extends TestCase {

    protected $useDatabase = true;

    public function testRegisterUser() {
        $user_helper = $this->app->make('TKAccounts\TestHelpers\UserHelper')->setTestCase($this);

        // create a user
        $user = $user_helper->registerNewUser($this->app);
        $this->assertNotNull($user);

        // make sure the user exists
        $this->assertTrue($user_helper->userExistsInDB($user));
    }

    public function testRegistrationLogsInUser() {
        $user_helper = $this->app->make('TKAccounts\TestHelpers\UserHelper')->setTestCase($this);

        // create a user
        $user = $user_helper->registerNewUser($this->app);
        $this->assertNotNull($user);

        // make sure user is logged in
        $user_id = $this->app['auth']->id();
        $this->assertGreaterThan(0, $user_id);

    }




}

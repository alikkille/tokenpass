<?php

use TKAccounts\TestHelpers\UserHelper;
use Illuminate\Support\Facades\App;

/*
* DashboardTest
*/
class DashboardTest extends TestCase {


    protected $useDatabase = true;

    public function testDashboardShowsName() {
        $user_helper = $this->app->make('TKAccounts\TestHelpers\UserHelper')->setTestCase($this);

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user = $user_helper->loginWithForm($this->app);

        // can get to dashboard
        $response = $this->call('GET', '/user/dashboard');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('johndoe', $response->getContent());
        // $this->assertContains('johndoe@devonweller.com', $response->getContent());
    }



}

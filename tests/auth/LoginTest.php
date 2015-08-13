<?php

use TKAccounts\TestHelpers\UserHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/*
* LoginTest
*/
class LoginTest extends TestCase {

    protected $use_database = true;

    public function testUserLogin() {
        $user_helper = app('UserHelper')->setTestCase($this);

        // create a user
        $user = $user_helper->createNewUser();
        $this->assertNotNull($user);

        // login
        $user = $user_helper->loginWithForm($this->app);
        $this->assertNotNull($user);

        // check session
        $user_id = $this->app['auth']->id();
        $this->assertGreaterThan(0, $user_id);

        // check db
        $this->assertTrue($user_helper->userExistsInDB($user));
    }


    public function testLoginRedirectsToPreviousPage() {
        $user_helper = app('UserHelper')->setTestCase($this);

        // create a new user
        $user = $user_helper->createNewUser();
        $this->assertNotNull($user);


        // try to hit user dashboard
        $response = $this->call('GET', '/dashboard');

        // check that it is a 302 redirect to the login page
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://localhost/auth/login', $response->getTargetUrl());

        // get the last sesssion
        $session = $this->app['auth']->getSession();

        // login
        $response = $user_helper->sendLoginRequest($this->app, $session);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://localhost/dashboard', $response->getTargetUrl());

    }


    public function testDashboardAccess() {
        $user_helper = app('UserHelper')->setTestCase($this);

        // guest should not have access to dashboard
        $response = $this->call('GET', '/dashboard');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://localhost/auth/login', $response->getTargetUrl());

        // create a user
        $user = $user_helper->createNewUser();
        $this->assertNotNull($user);

        // login
        $user = $user_helper->loginWithForm($this->app);
        $this->assertNotNull($user);

        // can get to dashboard
        $response = $this->call('GET', '/dashboard');
        $this->assertEquals(200, $response->getStatusCode());

    }



}

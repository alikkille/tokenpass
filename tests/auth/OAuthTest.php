<?php

use TKAccounts\TestHelpers\UserHelper;
use Illuminate\Support\Facades\App;

/*
* OAuthTest
*/
class OAuthTest extends TestCase {


    protected $use_database = true;

    public function testOAuthAuthorizeFormRequest() {
        $user_helper = app('UserHelper')->setTestCase($this);
        $oauth_helper = app('OAuthHelper')->setTestCase($this);

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user = $user_helper->loginWithForm($this->app);

        // check loading authorize form
        $response = $this->call('GET', '/oauth/authorize', $oauth_helper->getOauthAuthorizationParameters());
        // print "\$response: $response\n";
        // $json = json_decode($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<form', $response->getContent());

    }

    public function testOAuthAuthorizeFormResponse() {
        $user_helper = app('UserHelper')->setTestCase($this);
        $oauth_helper = app('OAuthHelper')->setTestCase($this);

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user = $user_helper->loginWithForm($this->app);

        // check loading authorize form
        $response = $this->call('GET', '/oauth/authorize', $oauth_helper->getOauthAuthorizationParameters());
        $response = $oauth_helper->submitGrantAccessForm($this->app);
        // echo $response."\n";

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('http://example1.com/callback?code=', $response->getTargetUrl());
        $this->assertContains('&state=somerandomstate', $response->getTargetUrl());
    }

    public function testGetUserFromClient() {
        $oauth_helper = app('OAuthHelper')->setTestCase($this);
        $auth_code = $oauth_helper->getClientAuthorizationCode($this->app);
        $this->assertNotEmpty($auth_code);
        // Log::info("\$auth_code=".json_encode($auth_code, 192));

        // now get an access token
        $access_token = $oauth_helper->getAccessToken($this->app, $auth_code);
        $this->assertNotEmpty($access_token);
        // Log::info("\$access_token=".json_encode($access_token, 192));

        // now get the user
        $user_json = $oauth_helper->getUserWithAccessToken($this->app, $access_token);
        $this->assertNotEmpty($user_json);
        $this->assertEquals('johndoe', $user_json['username']);
    }


    public function setUpDb()
    {
        parent::setUpDb();

        $this->seed('OAuthClientsTableSeeder');
        $this->seed('OAuthScopesTableSeeder');

        return;
    }

}

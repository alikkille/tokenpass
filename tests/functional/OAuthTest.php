<?php

use TKAccounts\TestHelpers\UserHelper;
use Illuminate\Support\Facades\App;

/*
* OAuthTest
*/
class OAuthTest extends TestCase {


    protected $useDatabase = true;

    public function testOAuthAuthorizeForm() {
        $user_helper = $this->app->make('TKAccounts\TestHelpers\UserHelper')->setTestCase($this);

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user = $user_helper->login($this->app);

        // check loading authorize form
        $oauth_vars = [
            'client_id'     => 'client1id',
            'redirect_uri'  => 'http://example1.com/callback',
            'scope'         => 'user,email',
            'state'         => 'somerandomstate',
            'response_type' => 'code',
        ];
        $response = $this->call('GET', '/oauth/authorize', $oauth_vars);
        // $json = json_decode($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<form', $response->getContent());

    }

    public function testOAuthAuthorizeFormResponse() {
        $user_helper = $this->app->make('TKAccounts\TestHelpers\UserHelper')->setTestCase($this);
        $oauth_helper = $this->app->make('TKAccounts\TestHelpers\OAuthHelper');

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user = $user_helper->login($this->app);

        // check loading authorize form
        $oauth_vars = [
            'client_id'     => 'client1id',
            'redirect_uri'  => 'http://example1.com/callback',
            'scope'         => 'user,email',
            'state'         => 'somerandomstate',
            'response_type' => 'code',
        ];
        $response = $oauth_helper->submitGrantAccessForm($this->app, $oauth_vars);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('http://example1.com/callback?code=', $response->getTargetUrl());
        $this->assertContains('&state=somerandomstate', $response->getTargetUrl());
    }


    public function setUpDb()
    {
        parent::setUpDb();

        $this->seed('OAuthClientsTableSeeder');
        $this->seed('OAuthScopesTableSeeder');

        return;
    }

}

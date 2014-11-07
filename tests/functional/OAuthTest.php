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
        $vars = [
            'client_id'     => 'client1id',
            'redirect_uri'  => 'http://example1.com/callback',
            'scope'         => 'user,email',
            'state'         => 'somerandomstate',
            'response_type' => 'code',
        ];
        $response = $this->call('GET', '/oauth/authorize', $vars);
        // $json = json_decode($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<form', $response->getContent());
        // echo "\$json:\n".json_encode($json, 192)."\n";

        $this->app['session']->flush();
    }


    public function setUpDb()
    {
        parent::setUpDb();

        $this->seed('OAuthClientsTableSeeder');
        $this->seed('OAuthScopesTableSeeder');

        return;
    }

}

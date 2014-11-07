<?php

namespace TKAccounts\TestHelpers;

use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Log;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* OAuthHelper
*/
class OAuthHelper
{
    public function __construct(SessionManager $session_manager) {
        $this->session_manager = $session_manager;
    }

    public function submitGrantAccessForm($app, $oauth_auth_params=[], $session=null, $form_override_vars = []) {
        $form_vars = array_merge(['approve' => 'Grant Access'], $form_override_vars);

        // Request::create($uri, $method, $parameters, $cookies, $files, $server, $content)
        $oauth_auth_params = array_merge($this->getOauthAuthorizationParameters(), $oauth_auth_params);
        return $this->sendRequest($app, '/oauth/authorize?'.http_build_query($oauth_auth_params), 'POST', array_merge($form_vars, ['_token' => true]), $session);
    }

    public function getClientAuthorizationCode($app) {
        $user_helper = $app->make('TKAccounts\TestHelpers\UserHelper');

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user_helper->loginUser($app, $user);

        $response = $this->submitGrantAccessForm($app);
        PHPUnit::assertEquals(302, $response->getStatusCode());
        PHPUnit::assertContains('http://example1.com/callback?code=', $response->getTargetUrl());
        parse_str(parse_url($response->getTargetUrl())['query'], $return_params);
        return $return_params['code'];

        // $this->assertContains('&state=somerandomstate', $response->getTargetUrl());
    }

    // this is step 2 of the oauth flow - exchange the auth code for an access token
    public function getAccessToken($app, $auth_code) {
        $oauth_auth_params = $this->getOauthAuthorizationParameters();

        $post_vars = [
            'client_id'     => $oauth_auth_params['client_id'],
            'client_secret' => 'client1secret',
            'code'          => $auth_code,
            'redirect_uri'  => $oauth_auth_params['redirect_uri'],

            'grant_type'    => 'authorization_code',
        ];

        $response = $this->sendRequest($app, '/oauth/access-token', 'POST', $post_vars);

        PHPUnit::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        return $json['access_token'];
    }

    // use the access token to get the user details
    public function getUserWithAccessToken($app, $access_token) {
        $response = $this->sendRequest($app, '/oauth/user', 'GET', ['access_token' => $access_token]);
        PHPUnit::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        return $json;
    }

    public function getOauthAuthorizationParameters() {
        $oauth_auth_params = [
            'client_id'     => 'client1id',
            'redirect_uri'  => 'http://example1.com/callback',
            'scope'         => 'user,email',
            'state'         => 'somerandomstate',
            'response_type' => 'code',
        ];
        return $oauth_auth_params;
    }

    protected function sendRequest($app, $uri, $method, $vars, $session=null) {
        $request = Request::create($uri, $method, $vars);
        $this->ensureSession($request);
        return $app->make('TKAccounts\TestHelpers\TestKernel')->handle($request);
    }

    protected function ensureSession($request, $session=null) {
        if (!$session) {
            $session = $this->session_manager->driver();
            $session->start();
        }

        $request->setSession($session);
    }
}

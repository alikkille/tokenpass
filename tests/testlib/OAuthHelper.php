<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use PHPUnit_Framework_Assert as PHPUnit;

/*
* OAuthHelper
*/
class OAuthHelper
{
    public function __construct(SessionManager $session_manager)
    {
        $this->session_manager = $session_manager;
    }

    public function setTestCase(TestCase $test_case)
    {
        $this->test_case = $test_case;

        return $this;
    }

    public function submitGrantAccessForm($app, $oauth_auth_params = [], $session = null, $form_override_vars = [])
    {
        $form_vars = array_merge(['approve' => 'Grant Access'], $form_override_vars);

        // Request::create($uri, $method, $parameters, $cookies, $files, $server, $content)
        $oauth_auth_params = array_merge($this->getOauthAuthorizationParameters(), $oauth_auth_params);

        // set the csrf token
        if (!isset($form_vars['_token'])) {
            $form_vars['_token'] = csrf_token();
        }

        return $this->sendRequest('POST', '/oauth/authorize?'.http_build_query($oauth_auth_params), array_merge($form_vars), $session);
    }

    public function getClientAuthorizationCode($app)
    {
        $user_helper = app('UserHelper');

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user_helper->loginUser($app, $user);

        // build the authorize form to populate the csrf token
        $response = $this->test_case->call('GET', '/oauth/authorize', $this->getOauthAuthorizationParameters());

        // submit the grant access form
        $response = $this->submitGrantAccessForm($app);
        PHPUnit::assertEquals(302, $response->getStatusCode());
        PHPUnit::assertContains('http://example1.com/callback?code=', $response->getTargetUrl());

        // return the authorization code
        parse_str(parse_url($response->getTargetUrl())['query'], $return_params);

        return $return_params['code'];
    }

    // this is step 2 of the oauth flow - exchange the auth code for an access token
    public function getAccessToken($app, $auth_code)
    {
        $oauth_auth_params = $this->getOauthAuthorizationParameters();

        $post_vars = [
            'client_id'     => $oauth_auth_params['client_id'],
            'client_secret' => 'client1secret',
            'code'          => $auth_code,
            'redirect_uri'  => $oauth_auth_params['redirect_uri'],

            'grant_type'    => 'authorization_code',
        ];

        $response = $this->sendRequest('POST', '/oauth/access-token', $post_vars);

        PHPUnit::assertEquals(200, $response->getStatusCode(), ''.$response);
        $json = json_decode($response->getContent(), true);

        return $json['access_token'];
    }

    // use the access token to get the user details
    public function getUserWithAccessToken($app, $access_token)
    {
        $response = $this->sendRequest('GET', '/oauth/user', ['access_token' => $access_token]);
        PHPUnit::assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);

        return $json;
    }

    public function getOauthAuthorizationParameters()
    {
        $oauth_auth_params = [
            'client_id'     => 'client1id',
            'redirect_uri'  => 'http://example1.com/callback',
            'scope'         => 'user,email',
            'state'         => 'somerandomstate',
            'response_type' => 'code',
        ];

        return $oauth_auth_params;
    }

    protected function sendRequest($method, $uri, $vars, $session = null)
    {
        // $response = $this->test_case->call($method, $uri, $vars);
        // return $response;

        $kernel = app('Illuminate\Contracts\Http\Kernel');

        $request = Request::create(
            $uri, $method, $vars
        );

        $this->ensureSession($request);

        $response = $kernel->handle($request);

        $kernel->terminate($request, $response);

        return $response;
    }

    protected function ensureSession($request, $session = null)
    {
        if (!$session) {
            $session = $this->session_manager->driver();
            $session->start();
        }

        $request->setSession($session);
    }
}

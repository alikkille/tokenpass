<?php

namespace TKAccounts\TestHelpers;

use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Log;

/*
* OAuthHelper
*/
class OAuthHelper
{
    public function __construct(SessionManager $session_manager) {
        $this->session_manager = $session_manager;
    }

    public function submitGrantAccessForm($app, $oauth_vars, $session=null, $form_override_vars = []) {
        $form_vars = array_merge(['approve' => 'Grant Access'], $form_override_vars);

        // Request::create($uri, $method, $parameters, $cookies, $files, $server, $content)
        $request = Request::create('/oauth/authorize?'.http_build_query($oauth_vars), 'POST', array_merge($form_vars, ['_token' => true]));


        // ensure session
        if (!$session) {
            $session = $this->session_manager->driver();
            $session->start();
        }
        $request->setSession($session);


        // send the login request with the test kernel which does not read the session and handles csrf tokens
        $kernel = $app->make('TKAccounts\TestHelpers\TestKernel');
        return $kernel->handle($request);
    }
}

<?php

use TKAccounts\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/*
* UserHelper
*/
class UserHelper
{
    public function __construct(SessionManager $session_manager) {
        $this->session_manager = $session_manager;
    }

    public function setTestCase(TestCase $test_case) {
        $this->test_case = $test_case;
        return $this;
    }


    public function registerNewUser($app, $user_override_vars = [])
    {
        $user = null;

        $user_vars = array_merge($this->defaultUserVars(), $user_override_vars);

        $response = $this->test_case->call('POST', '/auth/register', array_merge($user_vars, ['_token' => true, 'password_confirmation' => $user_vars['password']]));
        if ($response instanceof Illuminate\Http\RedirectResponse) {
            if ($errors_bag = $response->getSession()->get('errors')) {
                $errors = implode(", ", $errors_bag->all());
                throw new Exception("Found RedirectResponse with errors: ".$errors, 1);
            }
        }

        // get the session
        $auth = $app['auth'];
        $user_id = $auth->id();

        // get the user just created
        $user = User::find($user_id);
        if (!$user OR !$user->getKey()) {
            return null;
        }

        return $user;
    }

    public function createNewUser($user_override_vars = []) {
        $user_vars = array_merge($this->defaultUserVars(), $user_override_vars);

        // hash the password
        $user_vars['password'] = bcrypt($user_vars['password']);

        // create the user
        $user = User::create($user_vars);
        if (!$user->getKey()) { return null; }

        // get the user just created
        return $user;
    }

    public function loginWithForm($app, $user_override_vars = [])
    {
        $user = null;

        // get the response
        $response = $this->sendLoginRequest($app, null, $user_override_vars);

        // get the user id
        $auth = $app['auth'];
        $user_id = $auth->id();

        // get the user just created
        $user = User::find($user_id);
        if (!$user OR !$user->getKey()) {
            return null;
        }

        return $user;
    }

    public function loginUser($app, $user)
    {
        $app['auth']->login($user);
    }


    public function sendLoginRequest($app, $session=null, $user_override_vars = []) {
        $user_vars = array_merge($this->defaultUserVars(), $user_override_vars);
        $form_vars = [
            'username' => $user_vars['username'],
            'password' => $user_vars['password'],
        ];


        $response = $this->test_case->call('POST', '/auth/login', array_merge($form_vars, ['_token' => true,]));
        if ($response instanceof Illuminate\Http\RedirectResponse) {
            if ($errors_bag = $response->getSession()->get('errors')) {
                $errors = implode(", ", $errors_bag->all());
                throw new Exception("Login Found RedirectResponse with errors: ".$errors, 1);
            }
        }

        return $response;



        // // Request::create($uri, $method, $parameters, $cookies, $files, $server, $content)
        // $request = Request::create('/auth/login', 'POST', array_merge($form_vars, ['_token' => true]));

        // // ensure session
        // if (!$session) {
        //     $session = $this->session_manager->driver();
        //     $session->start();
        // }
        // $request->setSession($session);


        // // send the login request with the test kernel which does not read the session
        // $kernel = $app->make('TKAccounts\TestHelpers\TestKernel');
        // return $kernel->handle($request);
    }


    public function userExistsInDB(User $user) {
        $loaded_user = User::find($user->getKey());
        return ($loaded_user->getKey() AND $loaded_user->getKey() == $user->getKey());
    }

    public function defaultUserVars() {
        return [
            'name'     => 'John Doe',
            'username' => 'johndoe',
            'email'    => 'johndoe@devonweller.com',
            'password' => 'abc123456',
        ];
    }
}

<?php

namespace TKAccounts\TestHelpers;

use TKAccounts\Models\User;
use TKAccounts\Repositories\UserRepository;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/*
* OAuthHelper
*/
class OAuthHelper
{
    public function __construct() {
    }



    public function defaultUserVars() {
        return [
            'username' => 'johndoe',
            'email'    => 'johndoe@devonweller.com',
            'password' => 'abc123456',
        ];
    }
}

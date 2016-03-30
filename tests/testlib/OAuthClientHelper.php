<?php

use Illuminate\Support\Facades\Log;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* OAuthClientHelper
*/
class OAuthClientHelper
{
    public function __construct() {
    }

    public function createSampleOAuthClient($override_vars=[]) {
        // create an oauth client
        $oauth_client = app('TKAccounts\Repositories\OAuthClientRepository')->create(array_merge([
            'id'     => 'MY_API_TOKEN',
            'secret' => 'MY_SECRET',
            'name'   => 'client one',
        ], $override_vars));

        return $oauth_client;
    }


}

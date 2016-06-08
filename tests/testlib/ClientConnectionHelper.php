<?php

use Illuminate\Support\Facades\Log;
use TKAccounts\Models\OAuthClient;
use TKAccounts\Models\User;

/*
* ClientConnectionHelper
*/
class ClientConnectionHelper
{

    public function newSampleConnection(User $user=null, OAuthClient $client=null, $override_vars=[]) {
        if ($user === null)   { $user = app('UserHelper')->getOrCreateSampleUser(); }
        if ($client === null) { $client = app('OAuthClientHelper')->createRandomOAuthClient(); }

        $insert_vars = [
            'user_id'   => $user['id'],
            'client_id' => $client['id'],
        ];

        $connection = app('TKAccounts\Repositories\ClientConnectionRepository')->create($insert_vars);
        return $connection;
    }

}

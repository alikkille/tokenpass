<?php

use TKAccounts\Models\OAuthClient;
use TKAccounts\Models\User;

/*
* ClientConnectionHelper
*/
class ClientConnectionHelper
{
    public function newSampleConnection(User $user = null, OAuthClient $client = null, $override_vars = [])
    {
        $insert_vars = $this->newSampleConnectionVars($user, $client, $override_vars);
        $connection = app('TKAccounts\Repositories\ClientConnectionRepository')->create($insert_vars);

        return $connection;
    }

    public function newSampleConnectionVars(User $user = null, OAuthClient $client = null, $override_vars = [])
    {
        if ($user === null) {
            $user = app('UserHelper')->getOrCreateSampleUser();
        }
        if ($client === null) {
            $client = app('OAuthClientHelper')->createRandomOAuthClient();
        }

        return [
            'user_id'   => $user['id'],
            'client_id' => $client['id'],
        ];
    }
}

<?php

namespace TKAccounts\Repositories;

use Exception;
use Illuminate\Support\Facades\Log;
use TKAccounts\Models\OAuthClient;
use TKAccounts\Models\User;
use Tokenly\LaravelApiProvider\Repositories\APIRepository;

/*
* ClientConnectionRepository
*/
class ClientConnectionRepository extends APIRepository
{

    protected $model_type = 'TKAccounts\Models\ClientConnection';


    public function connectUserToClient(User $user, OAuthClient $client) {
        return $this->create([
            'user_id'   => $user['id'],
            'client_id' => $client['id'],
        ]);
    }

    public function disconnectUserFromClient(User $user, OAuthClient $client) {
        $client_connection = $this->findClientConnection($user, $client);

        if ($client_connection) {
            $this->delete($client_connection);
        }
    }

    public function isUserConnectedToClient(User $user, OAuthClient $client) {
        $client_connection = $this->findClientConnection($user, $client);
        return ($client_connection ? true : false);
    }

    public function findClientConnection(User $user, OAuthClient $client) {
        return $this->prototype_model
            ->where('user_id', $user['id'])
            ->where('client_id', $client['id'])
            ->first();
    }

    public function buildConnectedClientDetialsForUser(User $user) {
        $client_repository = app('TKAccounts\Repositories\OAuthClientRepository');

        $out = [];
        foreach ($this->prototype_model->where('user_id', $user['id'])->get() as $client_connection) {
            $out[] = [
                'connection' => $client_connection,
                'client'     => $client_repository->findByID($client_connection['client_id']),
            ];
        }
        return $out;
    }

}

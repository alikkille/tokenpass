<?php

namespace TKAccounts\Repositories;

use DB;
use TKAccounts\Models\OAuthClient;
use TKAccounts\Models\OAuthScope;
use TKAccounts\Models\User;
use Tokenly\LaravelApiProvider\Repositories\APIRepository;

/*
* ClientConnectionRepository
*/
class ClientConnectionRepository extends APIRepository
{
    protected $model_type = 'TKAccounts\Models\ClientConnection';

    public function connectUserToClient(User $user, OAuthClient $client, $scopes = [])
    {
        $create = $this->create([
            'user_id'   => $user['id'],
            'client_id' => $client['id'],
        ]);
        if (count($scopes) > 0) {
            foreach ($scopes as $scope) {
                if (!is_string($scope)) {
                    if (method_exists($scope, 'getId')) {
                        $scope = $scope->getId();
                    } else {
                        $scope = $scope->id;
                    }
                }
                $getScope = OAuthScope::find($scope);
                if ($getScope) {
                    $id = $create->id;
                    DB::table('client_connection_scopes')->insert(['connection_id' => $id, 'scope_id' => $getScope->uuid]);
                }
            }
        }

        return $create;
    }

    public function disconnectUserFromClient(User $user, OAuthClient $client)
    {
        $client_connection = $this->findClientConnection($user, $client);

        if ($client_connection) {
            $this->delete($client_connection);
        }
    }

    public function isUserConnectedToClient(User $user, OAuthClient $client)
    {
        $client_connection = $this->findClientConnection($user, $client);

        return $client_connection ? true : false;
    }

    public function findClientConnection(User $user, OAuthClient $client)
    {
        return $this->prototype_model
            ->where('user_id', $user['id'])
            ->where('client_id', $client['id'])
            ->first();
    }

    public function buildConnectedClientDetialsForUser(User $user)
    {
        $client_repository = app('TKAccounts\Repositories\OAuthClientRepository');

        $out = [];
        foreach ($this->prototype_model->where('user_id', $user['id'])->get() as $client_connection) {
            $out[] = [
                'connection' => $client_connection,
                'client'     => $client_repository->findByID($client_connection['client_id']),
                'scopes'     => $client_connection->scopes(),
            ];
        }

        return $out;
    }

    public function findByClientId($client_id)
    {
        return $this->prototype_model->where('client_id', $client_id)->get();
    }

    public function getConnectionScopes($connection_id)
    {
        $get = $this->prototype_model->where('id', $connection_id)->first();
        if ($get) {
            return $get->scopes();
        }

        return [];
    }
}

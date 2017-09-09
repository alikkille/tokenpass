<?php

namespace TKAccounts\Models;

use DB;
use Tokenly\LaravelApiProvider\Model\APIModel;

class ClientConnection extends APIModel
{
    protected $api_attributes = ['id'];

    public function client()
    {
        return $this->belongsTo('TKAccounts\Models\OAuthClient');
    }

    public function user()
    {
        return $this->belongsTo('TKAccounts\Models\User');
    }

    public function scopes()
    {
        $get = DB::table('client_connection_scopes as c')->select('s.*')
        ->leftJoin('oauth_scopes as s', 'c.scope_id', '=', 's.uuid')
        ->where('c.connection_id', $this->id)->orderBy('s.id', 'asc')->get();
        if (!$get) {
            return [];
        }

        return $get;
    }
}

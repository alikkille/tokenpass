<?php

namespace TKAccounts\Models;

use Tokenly\LaravelApiProvider\Model\APIModel;
use Exception;

class ClientConnection extends APIModel {

    protected $api_attributes = ['id',];

    public function client() {
        return $this->belongsTo('TKAccounts\Models\OAuthClient');
    }

    public function user() {
        return $this->belongsTo('TKAccounts\Models\User');
    }
}

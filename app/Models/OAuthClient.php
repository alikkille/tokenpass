<?php

namespace TKAccounts\Models;

use Exception;
use Tokenly\LaravelApiProvider\Model\APIModel;

class OAuthClient extends APIModel {

    protected $table = 'oauth_clients';

    public $incrementing = false;

    protected $api_attributes = ['id','name'];

}

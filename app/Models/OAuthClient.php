<?php

namespace TKAccounts\Models;

use Exception;
use Tokenly\LaravelApiProvider\Model\APIModel;

class OAuthClient extends APIModel {

    protected $table = 'oauth_clients';

    protected $api_attributes = ['id',];

}

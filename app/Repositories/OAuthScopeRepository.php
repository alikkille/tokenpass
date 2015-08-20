<?php

namespace TKAccounts\Repositories;

use Tokenly\LaravelApiProvider\Repositories\APIRepository;
use Exception;

/*
* OAuthScopeRepository
*/
class OAuthScopeRepository extends APIRepository
{

    protected $model_type = 'TKAccounts\Models\OAuthScope';

}

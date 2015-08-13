<?php

namespace TKAccounts\Repositories;

use Exception;
use Tokenly\LaravelApiProvider\Repositories\APIRepository;

/*
* OAuthClientRepository
*/
class OAuthClientRepository extends APIRepository
{

    protected $model_type = 'TKAccounts\Models\OAuthClient';


    protected function modifyAttributesBeforeCreate($attributes) {
        $token_generator = app('Tokenly\TokenGenerator\TokenGenerator');

        // create a token
        if (!isset($attributes['id'])) {
            $attributes['id'] = $token_generator->generateToken(32, 'I');
        }
        if (!isset($attributes['secret'])) {
            $attributes['secret'] = $token_generator->generateToken(40, 'K');
        }

        return $attributes;
    }

}

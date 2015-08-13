<?php

namespace TKAccounts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Tokenly\LaravelApiProvider\Repositories\APIRepository;
use Tokenly\LaravelApiProvider\Contracts\APIUserRepositoryContract;
use Tokenly\TokenGenerator\TokenGenerator;
use Exception;

/*
* UserRepository
*/
class UserRepository extends APIRepository implements APIUserRepositoryContract
{

    protected $model_type = 'TKAccounts\Models\User';


    public function findByUser(User $user) {
        return $this->findByUserID($user['id']);
    }

    public function findByUserID($user_id) {
        return call_user_func([$this->model_type, 'where'], 'user_id', $user_id)->get();
    }


    public function findByEmail($email) {
        return call_user_func([$this->model_type, 'where'], 'email', $email)->first();
    }

    public function findByUsername($username) {
        return call_user_func([$this->model_type, 'where'], 'username', $username)->first();
    }


    public function findByAPIToken($api_token) {
        return null;
        // return call_user_func([$this->model_type, 'where'], 'username', $username)->first();
    }

    protected function modifyAttributesBeforeCreate($attributes) {
        $token_generator = new TokenGenerator();

        // hash any password
        if (isset($attributes['password']) AND strlen($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        } else {
            // un-guessable random password
            $attributes['password'] = Hash::make($token_generator->generateToken(34));
        }

        return $attributes;
    }

}

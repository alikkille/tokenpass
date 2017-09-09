<?php

namespace TKAccounts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use TKAccounts\Providers\CMSAuth\Util;
use Tokenly\LaravelApiProvider\Contracts\APIUserRepositoryContract;
use Tokenly\LaravelApiProvider\Repositories\APIRepository;
use Tokenly\TokenGenerator\TokenGenerator;

/*
* UserRepository
*/
class UserRepository extends APIRepository implements APIUserRepositoryContract
{
    protected $model_type = 'TKAccounts\Models\User';

    public function findByUser(User $user)
    {
        return $this->findByUserID($user['id']);
    }

    public function findByUserID($user_id)
    {
        return call_user_func([$this->model_type, 'where'], 'user_id', $user_id)->get();
    }

    public function findByEmail($email)
    {
        return call_user_func([$this->model_type, 'where'], 'email', $email)->first();
    }

    public function findByUsername($username)
    {
        return call_user_func([$this->model_type, 'where'], 'username', $username)->first();
    }

    public function findBySlug($slug)
    {
        return call_user_func([$this->model_type, 'where'], 'slug', $slug)->first();
    }

    public function findByAPIToken($api_token)
    {
        return call_user_func([$this->model_type, 'where'], 'apitoken', $api_token)->first();
    }

    public function findByConfirmationCode($confirmation_code)
    {
        return call_user_func([$this->model_type, 'where'], 'confirmation_code', $confirmation_code)->first();
    }

    protected function modifyAttributesBeforeCreate($attributes)
    {
        $token_generator = new TokenGenerator();

        // create a token
        if (!isset($attributes['apitoken'])) {
            $attributes['apitoken'] = $token_generator->generateToken(16, 'T');
        }
        if (!isset($attributes['apisecretkey'])) {
            $attributes['apisecretkey'] = $token_generator->generateToken(40, 'K');
        }

        // hash any password
        if (isset($attributes['password']) and strlen($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        } else {
            // un-guessable random password
            $attributes['password'] = Hash::make($token_generator->generateToken(34));
        }

        // username
        if (!isset($attributes['username'])) {
            $attributes['username'] = preg_replace('!^(.+?)@.*$!', '$1', $attributes['email']);
        }

        // slugify username
        if (!isset($attributes['slug'])) {
            $attributes['slug'] = Util::slugify($attributes['username']);
        }

        return $attributes;
    }

    protected function modifyAttributesBeforeUpdate($attributes, Model $model)
    {
        // hash any password
        if (isset($attributes['password']) and strlen($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        $secondfactor = 0;
        if (isset($attributes['second_factor']) and intval($attributes['second_factor']) == 1) {
            $secondfactor = 1;
        }
        $attributes['second_factor'] = $secondfactor;

        return $attributes;
    }
}

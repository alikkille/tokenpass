<?php

namespace TKAccounts\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Tokenly\LaravelApiProvider\Contracts\APIPermissionedUserContract;
use Tokenly\LaravelApiProvider\Model\APIUser;
use Tokenly\LaravelApiProvider\Model\Traits\Permissioned;

class User extends APIUser implements AuthenticatableContract, CanResetPasswordContract, APIPermissionedUserContract
{
    use Authenticatable, CanResetPassword, Permissioned;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'username', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    protected $dates = ['confirmation_code_expires_at'];

    protected $casts = [
        'privileges' => 'json',
    ];


    public function updateableFields() {
        return ['name', 'username', 'email', 'password'];
    }

    public function passwordMatches($plaintext_password) {
        return Hash::check($plaintext_password, $this['password']);
    }

    public function emailIsConfirmed() {
        return ($this['confirmed_email'] == $this['email']);
    }

}

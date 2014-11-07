<?php namespace TKAccounts\Models;

use Illuminate\Auth\Passwords\CanResetPasswordTrait;
use Illuminate\Auth\UserTrait;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\User as UserContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class User extends Model implements UserContract, CanResetPasswordContract {

	use UserTrait, CanResetPasswordTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

    protected $fillable = ['username','email', 'password'];


    public function setPasswordAttribute($plaintext_password) {
        $this->attributes['password'] = Hash::make($plaintext_password);
    }

}

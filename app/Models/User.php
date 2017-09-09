<?php

namespace TKAccounts\Models;

use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Mail;
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

    public function updateableFields()
    {
        return ['name', 'username', 'email', 'password'];
    }

    public function passwordMatches($plaintext_password)
    {
        return Hash::check($plaintext_password, $this['password']);
    }

    public function emailIsConfirmed()
    {
        return $this['confirmed_email'] == $this['email'];
    }

    public static function getByVerifiedAddress($data)
    {
        $get_user = DB::table('users')
            ->join('coin_addresses', 'coin_addresses.user_id', '=', 'users.id')
            ->where('coin_addresses.address', '=', $data)
            ->where('coin_addresses.verified', '=', 1)
            ->where('coin_addresses.login_toggle', '=', 1)
            ->where('coin_addresses.active_toggle', '=', 1)
            ->first();

        if (!$get_user) {
            return false;
        }

        return $get_user;
    }

    public static function getByOAuth($token)
    {
        $find_sesh = DB::table('oauth_access_tokens')->where('id', $token)->first();
        if (!$find_sesh) {
            return false;
        }
        $get_sesh = DB::table('oauth_sessions')->where('id', $find_sesh->session_id)->first();
        if (!$get_sesh or $get_sesh->owner_type != 'user') {
            return false;
        }
        $get_user = self::find($get_sesh->owner_id);
        if (!$get_user) {
            return false;
        }

        return ['user' => $get_user, 'session' => $get_sesh, 'access_token' => $find_sesh];
    }

    public static function notifyUser($userId, $view, $subject, $data)
    {
        $user = self::find($userId);
        $data['user'] = $user;

        return Mail::send($view, $data, function ($message) use ($user, $subject) {
            $message->to($user->email, $user->name)->subject($subject);
        });
    }

    public function notify($view, $subject, $data)
    {
        return self::notifyUser($this->id, $view, $subject, $data);
    }
}

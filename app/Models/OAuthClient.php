<?php

namespace TKAccounts\Models;

use Exception;
use Tokenly\LaravelApiProvider\Model\APIModel;

class OAuthClient extends APIModel {

    protected $table = 'oauth_clients';

    public $incrementing = false;

    protected $api_attributes = ['id','name'];
    
    public static function getUserClients($user_id)
    {
		$get = OAuthClient::where('user_id', $user_id)->get();
		if(count($get) == 0){
			return false;
		}
		return $get;
	}
	

}

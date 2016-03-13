<?php

namespace TKAccounts\Models;

use Exception;
use Tokenly\LaravelApiProvider\Model\APIModel, DB;

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
	
	public static function connectionHasScope($connect_id, $scope)
	{
		$get_scope = OAuthScope::find($scope);
		if(!$get_scope){
			throw new \Exception($scope.' scope not found in system');
		}		
		$scope_connect = DB::table('client_connection_scopes')->where('connection_id', $connect_id)->where('scope_id', $get_scope->uuid)->get();
		if(!$scope_connect OR count($scope_connect) == 0){
			return false;
		}
		return true;
	}
	

}

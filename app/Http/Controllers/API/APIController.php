<?php
namespace TKAccounts\Http\Controllers\API;
use DB, Exception, Response, Input, Hash;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;
use TKAccounts\Commands\ImportCMSAccount;
use TKAccounts\Commands\SendUserConfirmationEmail;
use TKAccounts\Commands\SyncCMSAccount;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\OAuthClient as AuthClient;
use TKAccounts\Models\OAuthScope as Scope;
use TKAccounts\Models\User, TKAccounts\Models\Address, TKAccounts\Models\UserMeta;
use TKAccounts\Providers\CMSAuth\CMSAccountLoader;
use TKAccounts\Repositories\ClientConnectionRepository;
use TKAccounts\Repositories\OAuthClientRepository;
use TKAccounts\Repositories\UserRepository;
use TKAccounts\Models\Provisional;
use Tokenly\TCA\Access;

class APIController extends Controller
{

    use DispatchesJobs;

    public function __construct(OAuthClientRepository $oauth_client_repository, ClientConnectionRepository $client_connection_repository, UserRepository $user_repository)
    {
        $this->oauth_client_repository      = $oauth_client_repository;
        $this->client_connection_repository = $client_connection_repository;  
        $this->user_repository = $user_repository;
    }

	public function checkTokenAccess($username)
	{
		$input = Input::all();
		$output = array();
		$http_code = 200;
		
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find($input['client_id']);
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			$output['result'] = false;
			return Response::json($output, 403);
		}
		$client_id = $input['client_id'];
		unset($input['client_id']);
        
        $include_provisional = true;
        if(isset($input['no_provisional']) AND !$input['no_provisional']){
            $include_provisional = false;
        }
		
		$getUser = User::where('username', $username)->first();
		if(!$getUser){
			//try falling back to CMS - temporary
			$cms = new CMSAccountLoader(env('CMS_ACCOUNTS_HOST'));
			$failed = false;
			try{
				$check = $cms->checkTokenAccess($username, Input::all());
			}
			catch(Exception $e){
				$failed = true;
			}
			if(!$failed){
				$output['result'] = $check;
			}
			else{
				$http_code = 404;
				$output['result'] = false;
				$output['error'] = 'Username not found';
			}
		}
		else{
			
			//make sure user has authenticated with this application at least once
			$find_connect = DB::table('client_connections')->where('user_id', $getUser->id)->where('client_id', $valid_client->id)->first();
			if(!$find_connect OR count($find_connect) == 0){
				$output['error'] = 'User has not authenticated yet with client application';
				$output['result'] = false;
				return Response::json($output, 403);
			}
			
			//look for the TCA scope
			$get_scope = Scope::find('tca');
			if(!$get_scope){
				$output['error'] = 'TCA scope not found in system';
				$output['result'] = false;
				return Response::json($output, 500);
			}
			
			
			//make sure scope is applied to client connection
			$scope_connect = DB::table('client_connection_scopes')->where('connection_id', $find_connect->id)->where('scope_id', $get_scope->uuid)->get();
			if(!$scope_connect OR count($scope_connect) == 0){
				$output['error'] = 'User does not have TCA scope applied for this client application';
				$output['result'] = false;
				return Response::json($output, 403);
			}
			
			$ops = array();
			$stack_ops = array();
			$checks = array();
			$tca = new Access;
			foreach($input as $k => $v){
				$exp_k = explode('_', $k);
				$k2 = 0;
				if(isset($exp_k[1])){
					$k2 = intval($exp_k[1]);
				}
				if($exp_k[0] == 'op'){
					$ops[$k2] = $v;
				}
				elseif($exp_k[0] == 'stackop'){
					$stack_ops[$k2] = strtoupper($v);
				}
				else{
					$checks[] = array('asset' => strtoupper($k), 'amount' => round(floatval($v) * 100000000)); //convert amount to satoshis
				}
			}
			$full_stack = array();
			foreach($checks as $k => $row){
				$stack_item = $row;
				if(isset($ops[$k])){
					$stack_item['op'] = $ops[$k];
				}
				else{
					$stack_item['op'] = '>='; //default to greater or equal than
				}
				if(isset($stack_ops[$k])){
					$stack_item['stackOp'] = $stack_ops[$k];
				}
				else{
					$stack_item['stackOp'] = 'AND';
				}
				$full_stack[] = $stack_item;
			}
			$balances = Address::getAllUserBalances($getUser->id, true, $include_provisional);
			$output['result'] = $tca->checkAccess($full_stack, $balances);
		}
		return Response::json($output, $http_code);
	}
	
	public function getAddresses($username, $force_refresh = false)
	{
		$output = array();
		$http_code = 200;
		$input = Input::all();
		
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			$output['result'] = false;
			return Response::json($output, 403);
		}		
		
		$user = User::where('username', $username)->first();
		if(!$user){
			$http_code = 404;
			$output['result'] = false;
			$output['error'] = 'Username not found';
			return Response::json($output, 404);
		}
		
		//make sure user has authenticated with this application at least once
		$find_connect = DB::table('client_connections')->where('user_id', $user->id)->where('client_id', $valid_client->id)->first();
		if(!$find_connect OR count($find_connect) == 0){
			$output['error'] = 'User has not authenticated yet with client application';
			$output['result'] = false;
			return Response::json($output, 403);
		}

		//make sure scope is applied to client connection
		
		try{
			$tca_scope = AuthClient::connectionHasScope($find_connect->id, 'tca');
			$priv_scope = AuthClient::connectionHasScope($find_connect->id, 'private-address');
		}
		catch(\Exception $e){
			$output['error'] = $e->getMessage();
			$output['result'] = false;
			return Response::json($output, 403);
		}
		
		if(!$tca_scope){
			$output['error'] = 'User does not have TCA scope applied for this client application';
			$output['result'] = false;
			return Response::json($output, 403);
		}
		
		$and_active = 1;
		$and_verified = 1;
		if(isset($input['oauth_token'])){
			$getUser = User::getByOAuth($input['oauth_token']);
			if($getUser AND $getUser['user']->id == $user->id){
				$priv_scope = true;
				$and_active = null;
				$and_verified = false;
			}
		}
		
		$use_public = 1;
		if($priv_scope AND !isset($input['public'])){
			$use_public = null;
		}
		
		if($force_refresh){
			Address::updateUserBalances($user->id);
		}
		
		$address_list = Address::getAddressList($user->id, $use_public, $and_active, $and_verified);
		if(!$address_list OR count($address_list) == 0){
			$output['addresses'] = array();
		}
		else{
			$balances = array();
			foreach($address_list as $address){
				$item = array('address' => $address->address, 'balances' => Address::getAddressBalances($address->id, true), 'public' => boolval($address->public), 'label' => $address->label);
				if($and_active == null){
					$item['active'] = boolval($address->active_toggle);
				}
				if(!$and_verified){
					$item['verified'] = boolval($address->verified);
				}
				$balances[] = $item;
			}
			$output['result'] = $balances;
		}
		return Response::json($output, $http_code);
	}
	
	public function getRefreshedAddresses($username)
	{
		return $this->getAddresses($username, true);
	}
	
	public function getAddressDetails($username, $address)
	{
		$output = array();
		$input = Input::all();
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			$output['result'] = false;
			return Response::json($output, 403);
		}		
		
		$user = User::where('username', $username)->first();
		if(!$user){
			$http_code = 404;
			$output['result'] = false;
			$output['error'] = 'Username not found';
			return Response::json($output, 404);
		}
		
		//make sure user has authenticated with this application at least once
		$find_connect = DB::table('client_connections')->where('user_id', $user->id)->where('client_id', $valid_client->id)->first();
		if(!$find_connect OR count($find_connect) == 0){
			$output['error'] = 'User has not authenticated yet with client application';
			$output['result'] = false;
			return Response::json($output, 403);
		}		
		
		$priv_scope = false;
		$logged_user = false;
		try{
			$tca_scope = AuthClient::connectionHasScope($find_connect->id, 'tca');
			$priv_scope = AuthClient::connectionHasScope($find_connect->id, 'private-address');
		}
		catch(\Exception $e){
			$output['error'] = $e->getMessage();
			$output['result'] = false;
			return Response::json($output, 403);
		}
		if(isset($input['oauth_token'])){
			$getUser = User::getByOAuth($input['oauth_token']);
			if($getUser AND $getUser['user']->id == $user->id){
				$priv_scope = true;
				$logged_user = true;
			}
		}
		
		if(!$logged_user){
			//check for TCA scope as well of no oauth_token
			if(!$tca_scope){
				$output['error'] = 'User does not have TCA scope applied for this client application';
				$output['result'] = false;
				return Response::json($output, 403);
			}			
		}		
		
		$getAddress = Address::where('user_id', $user->id)->where('address', $address)->first();
		if(!$getAddress OR ($getAddress->public == 0 AND !$priv_scope) OR ($getAddress->active_toggle == 0 AND !$logged_user)){
			$output['error'] = 'Address details not found';
			$output['result'] = false;
			return Response::json($output, 404);
		}
				
		$result = array();
		$result['type'] = $getAddress->type;
		$result['address'] = $getAddress->address;
		$result['label'] = $getAddress->label;
		$result['public'] = boolval($getAddress->public);
		$result['active'] = boolval($getAddress->active_toggle);
		$result['verified'] = boolval($getAddress->verified);
		$result['balances'] = Address::getAddressBalances($getAddress->id, true);
		if(!$result['verified']){
			$result['verify_code'] = Address::getVerifyCode($getAddress);
		}		
		$output['result'] = $result;
		
		return Response::json($output);
		
	}
	
	public function registerAddress()
	{
		$output = array();
		$input = Input::all();
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			$output['result'] = false;
			return Response::json($output, 403);
		}		
		

		$user = false;
		if(isset($input['oauth_token'])){
			$user = User::getByOAuth($input['oauth_token']);
		}			
				
		if(!$user){
			$output['error'] = 'Invalid user oauth token';
			$output['result'] = false;
			return Response::json($output, 403);
		}
		$user = $user['user'];
		
		$type = 'btc';
		if(isset($input['type'])){
			switch(strtolower($input['type'])){
				case 'btc':
				case 'bitcoin':
					$type = 'btc';
					break;
				default:
					$output['error'] = 'Invalid cryptocurrency type';
					$output['result'] = false;
					return Response::json($output, 400);
			}
		}
		
		if(!isset($input['address'])){
			$output['error'] = $type.' address required';
			$output['result'] = false;
			return Response::json($output, 400);
		}
		
		$address = trim($input['address']);
		switch($type){
			case 'btc':
				//validate address
				$xchain = app('Tokenly\XChainClient\Client');
				$validate = $xchain->validateAddress($address);
				if(!$validate OR !$validate['result']){
					$output['error'] = 'Please enter a valid bitcoin address';
					$output['result'] = false;
					return Response::json($output, 400);					
				}				
				break;
			
		}
		
		$label = '';
		if(isset($input['label'])){
			$label = trim(htmlentities($input['label']));
		}
		
		$public = 0;
		if(isset($input['public']) AND intval($input['public']) == 1){
			$public = 1;
		}
		
		$active = 1;
		if(isset($input['active']) AND intval($input['active']) == 0){
			$active = 0;
		}
		
		$getAddress = Address::where('user_id', $user->id)->where('address', $address)->first();
		if($getAddress){
			$output['error'] = 'Address already registered';
			$output['result'] = false;
			return Response::json($output, 400);	
		}
		
        $new = app('TKAccounts\Repositories\AddressRepository')->create([
			'user_id'       => $user->id,
			'type'          => $type,
			'address'       => $address,
			'label'         => $label,
			'public'        => $public,
			'active_toggle' => $active,
    	]);
		
		if(!$new){
			$output['error'] = 'Error registering address';
			$output['result'] = false;
			return Response::json($output, 500);
		}
		
		$result = array();
		$result['type'] = $type;
		$result['address'] = $address;
		$result['label'] = $label;
		$result['public'] = $public;
		$result['active'] = $active;
		$result['verify_code'] = Address::getVerifyCode($new);
		$output['result'] = $result;
		
		return Response::json($output);
	}
	
	public function editAddress($username, $address)
	{
		$output = array();
		$input = Input::all();
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			$output['result'] = false;
			return Response::json($output, 403);
		}		
		

		$user = false;
		if(isset($input['oauth_token'])){
			$user = User::getByOAuth($input['oauth_token']);
			if($user){
				$user = $user['user'];
			}
		}			
		$matchedUser = User::where('username', $username)->first();
				
		if(!$user OR !$matchedUser OR $user->id != $matchedUser->id){
			$output['error'] = 'Invalid user oauth token';
			$output['result'] = false;
			return Response::json($output, 403);
		}

		
		$getAddress = Address::where('user_id', $user->id)->where('address', $address)->first();
		if(!$getAddress){
			$output['error'] = 'Address not found';
			$output['result'] = false;
			return Response::json($output, 404);
		}	
		
		if(isset($input['label'])){
			$getAddress->label = trim(htmlentities($input['label']));
		}
		if(isset($input['public'])){
			$public = intval($input['public']);
			$getAddress->public = $public;
		}
		if(isset($input['active'])){
			$active = intval($input['active']);
			$getAddress->active_toggle = $active;
		}
		$save = $getAddress->save();
		if(!$save){
			$output['error'] = 'Error updating address';
			$output['result'] = false;
			return Response::json($output, 500);
		}
		
		return $this->getAddressDetails($username, $address);
	}
	
	public function deleteAddress($username, $address)
	{
		$output = array();
		$input = Input::all();
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			$output['result'] = false;
			return Response::json($output, 403);
		}		
		

		$user = false;
		if(isset($input['oauth_token'])){
			$user = User::getByOAuth($input['oauth_token']);
			if($user){
				$user = $user['user'];
			}
		}			
		$matchedUser = User::where('username', $username)->first();
				
		if(!$user OR !$matchedUser OR $user->id != $matchedUser->id){
			$output['error'] = 'Invalid user oauth token';
			$output['result'] = false;
			return Response::json($output, 403);
		}

		$getAddress = Address::where('user_id', $user->id)->where('address', $address)->first();
		if(!$getAddress){
			$output['error'] = 'Address not found';
			$output['result'] = false;
			return Response::json($output, 404);
		}			
		
		$delete = $getAddress->delete();
		if(!$delete){
			$output['error'] = 'Error deleting address';
			$output['result'] = false;
			return Response::json($output, 500);
		}
		
		$output['result'] = true;
		return Response::json($output);
	}
	
	
	public function verifyAddress($username, $address)
	{
		$output = array();
		$input = Input::all();
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			$output['result'] = false;
			return Response::json($output, 403);
		}		
		

		$user = false;
		if(isset($input['oauth_token'])){
			$user = User::getByOAuth($input['oauth_token']);
			if($user){
				$user = $user['user'];
			}
		}			
		$matchedUser = User::where('username', $username)->first();
				
		if(!$user OR !$matchedUser OR $user->id != $matchedUser->id){
			$output['error'] = 'Invalid user oauth token';
			$output['result'] = false;
			return Response::json($output, 403);
		}

		
		$getAddress = Address::where('user_id', $user->id)->where('address', $address)->first();
		if(!$getAddress){
			$output['error'] = 'Address not found';
			$output['result'] = false;
			return Response::json($output, 404);
		}	
		
		if($getAddress->verified == 1){
			$output['error'] = 'Address already verified';
			$output['result'] = true;
			return Response::json($output);
		}
		
		if(!isset($input['signature'])){
			$output['error'] = 'Verification signature required';
			$output['result'] = false;
			return Response::json($output, 400);
		}			
		
		$verify_code = Address::getVerifyCode($getAddress);
		
		$sig = $this->extract_signature($input['signature']);
		$xchain = app('Tokenly\XChainClient\Client');
		
		$verify_message = $xchain->verifyMessage($getAddress->address, $sig, $verify_code);
		$verified = false;
		if($verify_message AND $verify_message['result']){
			$verified = true;
		}
		
		if(!$verified){
			$output['error'] = 'Invalid verification signature!';
			$output['result'] = false;
			return Response::json($output, 400);
		}
		
		$getAddress->verified = 1;
		$save = $getAddress->save();
		if(!$save){
			$output['error'] = 'Error updating address';
			$output['result'] = false;
			return Response::json($output, 500);
		}

        // make sure to sync the new address with any xchain balances
        $getAddress->syncWithXChain();
		
		$output['result'] = true;
		return Response::json($output);
	}
	
	public function checkAddressTokenAccess($address)
	{
		$input = Input::all();
		$output = array();
		
		if(!isset($input['sig']) OR trim($input['sig']) == ''){
			$output['error'] = 'Proof-of-ownership signature required (first 10 characters of address)';
			$output['result'] = false;
			return Response::json($output, 400);
		}
		
		$xchain = app('Tokenly\XChainClient\Client');
		$validate = $xchain->validateAddress($address);	
		if(!$validate['result']){
			$output['error'] = 'Invalid address';
			$output['result'] = false;
			return Response::json($output, 400);
		}	
		
		$first_bits = substr($address, 0, 10);
		$check_sig = $xchain->verifyMessage($address, $input['sig'], $first_bits);
		if(!$check_sig['result']){
			$output['error'] = 'Invalid proof-of-ownership signature';
			$output['result'] = false;
			return Response::json($output, 403);
		}
		
		$sig = $input['sig'];
		unset($input['sig']);
		
		$tca = new Access(true);
		$ops = array();
		$stack_ops = array();
		$checks = array();
		$tca = new Access(true);
		foreach($input as $k => $v){
			$exp_k = explode('_', $k);
			$k2 = 0;
			if(isset($exp_k[1])){
				$k2 = intval($exp_k[1]);
			}
			if($exp_k[0] == 'op'){
				$ops[$k2] = $v;
			}
			elseif($exp_k[0] == 'stackop'){
				$stack_ops[$k2] = strtoupper($v);
			}
			else{
				$checks[] = array('asset' => strtoupper($k), 'amount' => round(floatval($v) * 100000000)); //convert amount to satoshis
			}
		}
		$full_stack = array();
		foreach($checks as $k => $row){
			$stack_item = $row;
			if(isset($ops[$k])){
				$stack_item['op'] = $ops[$k];
			}
			else{
				$stack_item['op'] = '>='; //default to greater or equal than
			}
			if(isset($stack_ops[$k])){
				$stack_item['stackOp'] = $stack_ops[$k];
			}
			else{
				$stack_item['stackOp'] = 'AND';
			}
			$full_stack[] = $stack_item;
		}
		

		$output['result'] = $tca->checkAccess($full_stack, false, $address);
		
		return Response::json($output);
	}
	
	public function requestOAuth()
	{
		$input = Input::all();
		$output = array();
		$error = false;
		
		if(!isset($input['state'])){
			$error = true;
			$output['error'] = 'State required';
		}
		
		if(!isset($input['client_id'])){
			$error = true;
			$output['error'] = 'Client ID required';
		}

		$client_id = $input['client_id'];
		$client = $this->oauth_client_repository->findById($client_id);
        if (!$client){ 
			$error = true;
			$output['error'] = "Unable to find oauth client for client ".$client_id;
		}				
		
		if(!isset($input['scope'])){
			$error = true;
			$output['error'] = 'Scope required';
		}
        $scope_param = Input::get('scope');
        $scopes = array();
		if($scope_param AND count($scopes) == 0){
			$scopes = explode(',', $scope_param);
		}		
		
		if(!isset($input['response_type']) OR $input['response_type'] != 'code'){
			$error = true;
			$output['error'] = 'Invalid response type';
		}	
		
		if(!isset($input['username']) OR trim($input['username']) == ''){
			$error = true;
			$output['error'] = 'Username required';
		}
		
		if(!isset($input['password']) OR trim($input['password']) == ''){
			$error = true;
			$output['error'] = 'Password required';
		}
		
		if($error){
			return Response::json($output);
		}		
		
		$user = User::where('username', $input['username'])->first();
		if(!$user){
			$error = true;
			$output['error'] = 'Invalid credentials';
		}
		else{
			$checkPass = Hash::check($input['password'], $user->password);
			if(!$checkPass){
				$error = true;
				$output['error'] = 'Invalid credentials';
			}
		}
		
		if(!$error){
			$already_connected = $this->client_connection_repository->isUserConnectedToClient($user, $client);
			if(!$already_connected){	
				$grant_access = false;
				if(isset($input['grant_access']) AND intval($input['grant_access']) === 1){
					$grant_access = true;
				}
				if(!$grant_access){
					$error = true;
					$output['error'] = 'Application denied access to account';
				}
			}	
		}	
		
		if(!$error){
			$code_params =  Authorizer::getAuthCodeRequestParams();
			$code_url = Authorizer::issueAuthCode('user', $user->id, $code_params);
			$parse = parse_str(parse_url($code_url)['query'], $parsed);
			$output['code'] = $parsed['code'];
			$output['state'] = $parsed['state'];			
			if(!$already_connected){
				$this->client_connection_repository->connectUserToClient($user, $client, $scopes);
			}
		}
		
		return Response::json($output);
	}
	
	public function getOAuthToken()
	{
		$output = array();
        try {
			$output = Authorizer::issueAccessToken();
        } catch (\Exception $e) {
            Log::error("Exception: ".get_class($e).' '.$e->getMessage());
			$output['error'] = 'Failed getting access token';
        }
        return Response::json($output);
	}
	
	public function registerAccount()
	{
		$input = Input::all();
		$output = array();
		$error = false;
		$output['result'] = false;
		
		if(!isset($input['client_id']) OR !AuthClient::find($input['client_id'])){
			$error = true;
			$output['error'] = 'Invalid API client ID';
		}

		if(!isset($input['username']) OR trim($input['username']) == ''){
			$error = true;
			$output['error'] = 'Username required';
		}
		
		if(!isset($input['password']) OR trim($input['password']) == ''){
			$error = true;
			$output['error'] = 'Password required';
		}
		
		if(!isset($input['email']) OR trim($input['email']) == ''){
			$error = true;
			$output['error'] = 'Email required';
		}
		
		if($error){
			return Response::json($output);
		}
		
		$data['username'] = $input['username'];
		$data['password'] = $input['password'];
		$data['email'] = $input['email'];
		$data['name'] = '';
		if(isset($input['name'])){
			$data['name'] = $input['name'];
		}	
		
        // we can't create a new user with an existing LTB username
        $loader = app('TKAccounts\Providers\CMSAuth\CMSAccountLoader');
        if ($loader->usernameExists($data['username'])) {
			$error = true;
            $output['error'] = 'This username was found at LetsTalkBitcoin.com.  Please login with your existing credentials instead of creating a new account.';
        }	
        
        $find_user = User::where('email', $data['email'])->orWhere('username', $data['username'])->first();
        if($find_user){
			$error = true;
			if($find_user->username == $data['username']){
				$output['error'] = 'Username already taken';
			}
			else{
				$output['error'] = 'Email already taken';
			}
		}		
		
		if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
			$error = true;
			$output['error'] = 'Invalid email address';
		}
		
		if(!$error){
			try{
				$register = $this->user_repository->create([
						'name'     => $data['name'],
						'username' => $data['username'],
						'email'    => $data['email'],
						'password' => $data['password'],
					]);
			}
			catch(\Exception $e){
				$register = false;
				$output['error'] = 'Error registering account';
			}
			
			if($register){
				$output['result'] = array('id' => $register->uuid, 'username' => $register->username, 'email' => $register->email);
				$this->dispatch(new SendUserConfirmationEmail($register));
			}
		}
		
		return Response::json($output);
	}
	
	public function updateAccount()
	{
		$input = Input::all();
		$output = array();
		$error = false;
		$output['result'] = false;
		
		if(!isset($input['client_id']) OR !AuthClient::find($input['client_id'])){
			$error = true;
			$output['error'] = 'Invalid API client ID';
		}
		
		if(!isset($input['user_id'])){
			$error = true;
			$output['error'] = 'User ID required';
		}
		
		if(!isset($input['token'])){
			$error = true;
			$output['error'] = 'Access Token required';
		}
		
		if(!isset($input['current_password'])){
			$error = true;
			$output['error'] = 'Current password required';
		}
		
		if($error){
			return Response::json($output);
		}		
		
		$user = User::where('uuid', $input['user_id'])->first();
		
		$get_token = DB::table('oauth_access_tokens')->where('id', $input['token'])->first();
		$valid_access = false;
		if($get_token AND $user){
			$get_sesh = DB::table('oauth_sessions')->where('id', $get_token->session_id)->first();
			if($get_sesh AND $get_sesh->client_id == $input['client_id'] AND $get_sesh->owner_id == $user->id){
				$valid_access = true;
			}
		}
		if(!$valid_access){
			$output['error'] = 'Invalid access token, client ID or user ID';
			return Response::json($output); 
		}
		
		$check = Hash::check($input['current_password'], $user->password);
		if(!$check){
			$output['error'] = 'Invalid password';
			return Response::json($output); 
		}		
		
		$to_change = array();
		if(isset($input['name']) AND $input['name'] != $user->name){
			$to_change['name'] = trim($input['name']);
		}		
		if(isset($input['email']) AND trim($input['email']) != '' AND $input['email'] != $user->email){
			$to_change['email'] = $input['email'];
		}
		if(isset($input['password']) AND trim($input['password']) != ''){
			$to_change['password'] = $input['password'];
		}		
		
		if(count($to_change) == 0){
			$output['error'] = 'No changes to make';
			return Response::json($output); 
		}
		$changed = array_keys($to_change);
		foreach($to_change as $k => $v){
			switch($k){
				case 'name':
					$user->name = $v;
					break;
				case 'email':
					$user->email = $v;
					$this->dispatch(new SendUserConfirmationEmail($user));
					break;
				case 'password':
					$user->password = Hash::make($v);
					break;
			}
		}
		
		$save = $user->save();
		if(!$save){
			$output['error'] = 'Error saving updated account information';
		}
		else{
			$output['result'] = 'success';
		}
		
		return Response::json($output);
	}
	
	public function invalidateOAuth()
	{
		$input = Input::all();
		$output = array();
		$output['result'] = false;
		if(!isset($input['client_id']) OR !AuthClient::find($input['client_id'])){
			$output['error'] = 'Invalid API client ID';
			return Response::json($output);
		}
		if(!isset($input['token'])){
			$output['error'] = 'OAuth token required';
			return Response::json($output);
		}
		$get = User::getByOAuth($input['token']);
		if(!$get){
			$output['error'] = 'Invalid OAuth token';
			return Response::json($output);
		}
		if($get['session']->client_id != $input['client_id']){
			$output['error'] = 'Session does not belong to client';
			return Response::json($output);
		}
		$browser_sesh = UserMeta::getMeta($get['user']->id, 'session_id');
		if($browser_sesh){
			DB::table('sessions')->where('id', $browser_sesh)->delete();
		}		
		DB::table('oauth_access_tokens')->where('id', $get['access_token']->id)->delete();
		DB::table('oauth_sessions')->where('id', $get['session']->id)->delete();
		$output['result'] = true;
		return Response::json($output);
	}

	/**
	 * This only verifies the user by login and password.  It does not confer any grants
	 * @param  Request $request The HTTP Request
	 * @return JsonResponse     The HTTP Response
	 */
	public function loginWithUsernameAndPassword(Request $request) {
		$this->validate($request, [
            'client_id' => 'required',
            'username'  => 'required|max:255',
            'password'  => 'required|max:255',
		]);

		// require a valid client_id
		$client_id = $request->input('client_id');
		$valid_client = AuthClient::find($client_id);
		if (!$valid_client) {
			$error = 'Invalid API client ID';
			return new JsonResponse(['message' => $error, 'errors' => [$error]], 403);
		}

		$credentials = $request->only(['username','password']);
		$auth_controller = app('TKAccounts\Http\Controllers\Auth\AuthController');
		list($login_error, $was_logged_in) = $auth_controller->performLoginLogic($credentials, false);
		if ($was_logged_in) {
			$user = Auth::user();
			return new JsonResponse([
				'id'              => $user['uuid'],
				'name'            => $user['name'],
				'username'        => $user['username'],
				'email'           => $user['email'],
				'confirmed_email' => $user['confirmed_email'],
			], 200);
		}

		if (!$login_error) { $login_error = 'failed to login'; }
		return new JsonResponse(['message' => $login_error, 'errors' => [$login_error]], 422);
	}

    protected function buildFailedValidationResponse(Request $request, array $errors)
	{
	    if (is_array($errors)) {
	        $error_strings = [];
	        foreach($errors as $error) {
	            $error_strings = array_merge($error_strings, array_values($error));
	        }
	        $message = implode(" ", $error_strings);
	        $errors = $error_strings;
	    } else {
	        $message = $errors;
	        $errors = [$errors];
	    }
	    return new JsonResponse(['message' => $message, 'errors' => $errors], 422);
	}
	
	
	protected function extract_signature($text,$start = '-----BEGIN BITCOIN SIGNATURE-----', $end = '-----END BITCOIN SIGNATURE-----')
	{
		$inputMessage = trim($text);
		if(strpos($inputMessage, $start) !== false){
			//pgp style signed message format, extract the actual signature from it
			$expMsg = explode("\n", $inputMessage);
			foreach($expMsg as $k => $line){
				if($line == $end){
					if(isset($expMsg[$k-1])){
						$inputMessage = trim($expMsg[$k-1]);
					}
				}
			}
		}
		return $inputMessage;	
	}	
	
	public function lookupUserByAddress($address)
	{
		$output = array();
		$output['result'] = false;
		$input = Input::all();
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			return Response::json($output, 403);
		}			
        
        if(isset($input['address_list']) AND is_array($input['address_list'])){
            //lookup multiple users at once
            $get = Address::select('address', 'user_id', 'public', 'active_toggle', 'verified')->whereIn('address', $input['address_list'])->get();
            if($get){
                $user_ids = array();
                foreach($get as $k => $row){
                    if($row->public == 1 AND $row->verified == 1 AND $row->active_toggle == 1 ){
                        if(!in_array($row->user_id, $user_ids)){
                            $user_ids[] = $row->user_id;
                        }
                    }
                    else{
                        unset($get[$k]);
                        continue;
                    }
                }
                $output['users'] = array();
                $get_users = User::select('id', 'username', 'email')->whereIn('id', $user_ids)->get();
                if($get_users){
                    foreach($get as $row){
                        foreach($get_users as $user){
                            if($user->id == $row->user_id){
                                $output['users'][$row->address] = 
                                    array('username' => $user->username, 'address' => $row->address,
                                          'email' => $user->email
                                         );
                                continue 2;
                            }
                        }
                    }
                }
                if(count($output['users']) > 0){
                    $output['result'] = true;
                }
            }
        }
        else{
            //lookup single address/user
            $get = Address::where('address', $address)->first();
            if($get){
                if($get->public == 0 OR $get->active_toggle == 0 OR $get->verified == 0){
                    $get = false;
                }
            }
            if(!$get){
                $output['error'] = 'User not found';
                return Response::json($output, 404);
            }
            
            $user = User::find($get->user_id);
            
            $result = array();
            $result['username'] = $user->username;
            $result['address'] = $get->address;
            $result['email'] = $user->email;
            $output['result'] = $result;
        }
		return Response::json($output);
	}
	
	public function lookupAddressByUser($username)
	{
		$output = array();
		$output['result'] = false;
		$input = Input::all();
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			return Response::json($output, 403);
		}			
		$get = User::where('username', $username)->first();
		if(!$get){
			$output['error'] = 'User not found';
			return Response::json($output, 404);
		}
		
		$addresses = Address::getAddressList($get->id, 1, 1, true);
		if(!$addresses OR count($addresses) == 0){
			$output['error'] = 'User not found'; //user is hidden
			return Response::json($output, 404);
		}
		$result = array();
		$result['username'] = $get->username;
		$result['address'] = $addresses[0]->address;
        $result['email'] = $get->email;
		$output['result'] = $result;
		return Response::json($output);
	}
	
	public function instantVerifyAddress($username)
	{
		$output = array();
		$output['result'] = false;
		
		//find user
		$user = User::where('username', $username)->first();
		if(!$user){
			$output['error'] = 'User not found'; 
			return Response::json($output, 404);
		}
		
		//check they included an address
		$verify_address = Input::get('address');
		if(!$verify_address OR trim($verify_address) == ''){
			$output['error'] = 'Address required'; 
			return Response::json($output, 400);
		}
		
		//get the message needed to verify and check inputs
		$verify_message = Address::getInstantVerifyMessage($user);
		$input_sig = Input::get('sig');
		$input_message = Input::get('msg');
		if(!$input_sig OR trim($input_sig) == ''){
			$output['error'] = 'sig required';
			return Response::json($output, 400);
		}
		if(!$input_message OR $input_message != $verify_message){
			$output['error'] = 'msg invalid';
			return Response::json($output, 400);
		}
		
		//verify signed message on xchain
		$xchain = app('Tokenly\XChainClient\Client');
		try{
			$verify = $xchain->verifyMessage($verify_address, $input_sig, $verify_message);
		}
		catch(Exception $e){
			$verify = false;
		}
		if(!$verify OR !isset($verify['result']) OR !$verify['result']){
			$output['error'] = 'signature invalid';
			return Response::json($output, 400);
		}
		
		//check to see if this address exists on their account
		$address = Address::where('user_id', $user->id)->where('address', $verify_address)->first();
		if(!$address){
			//register new address
			$address = app('TKAccounts\Repositories\AddressRepository')->create([
				'user_id'  => $user->id,
				'type'     => 'btc',
				'address'  => $verify_address,
				'verified' => 1,
			]);
			$save = ($address ? true : false);
		}
		else{
			//verify existing address
			$save = app('TKAccounts\Repositories\AddressRepository')->update($address, [
				'verified' => true,
			]);
		}
		if(!$save){
			$output['error'] = 'Error saving address';
			return Response::json($output, 500);
		}

        if ($address['verified']) {
            // make sure to sync the new address with any xchain balances
            $address->syncWithXChain();
        }

		
		UserMeta::setMeta($user->id, 'force_inventory_page_refresh', 1);
		UserMeta::setMeta($user->id, 'inventory_refresh_message', 'Address '.$address->address.' registered and verified!');
		$output['result'] = true;
		
		return Response::json($output);
	}
    
    public function registerProvisionalTCASourceAddress()
    {
		$output = array();
		$output['result'] = false;
		$input = Input::all();
        
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			return Response::json($output, 403);
        }
        
        //check inputs
        if(!isset($input['address'])){
            $output['error'] = 'Address required';
            return Response::json($output, 400);
        }
        
        if(!isset($input['proof'])){
            $output['error'] = 'Proof required';
            return Response::json($output, 400);
        }        

		//verify signed message on xchain
        $sig_message = Provisional::getProofMessage($input['address'], $input['client_id']);
		$xchain = app('Tokenly\XChainClient\Client');
		try{
			$verify = $xchain->verifyMessage($input['address'], $input['proof'], $sig_message);
		}
		catch(Exception $e){
			$verify = false;
		}
		if(!$verify OR !isset($verify['result']) OR !$verify['result']){
			$output['error'] = 'Proof signature invalid';
			return Response::json($output, 400);
		}
        
        $asset_list = null;
        if(isset($input['assets'])){
            if(!is_array($input['assets']) AND !is_object($input['assets'])){
                $input['assets'] = explode(',', $input['assets']);
            }
            $asset_list = json_encode($input['assets']);
        }
        
        $get = DB::table('provisional_tca_addresses')
                ->where('address', $input['address'])->where('client_id', $input['client_id'])
                ->first();
                
        $time = date('Y-m-d H:i:s');       
        if(!$get){
            //add new entry
            $data = array('address' => $input['address'], 'proof' => $input['proof'], 
                          'client_id' => $input['client_id'], 'assets' => $asset_list,
                          'created_at' => $time, 'updated_at' => $time);
            $update = DB::table('provisional_tca_addresses')->insert($data);
        }
        else{
            //update entry
            $data = array('proof' => $input['proof'], 'assets' => $asset_list, 'updated_at' => $time);
            $update = DB::table('provisional_tca_addresses')->where('id', $get->id)->update($data);
        }
        
        if(!$update){
			$output['error'] = 'Error registering provisional TCA address';
			return Response::json($output, 500);
        }
        
        $output['result'] = true;
        
        return Response::json($output);
    }
    
    public function deleteProvisionalTCASourceAddress($address)
    {
		$output = array();
		$output['result'] = false;
		$input = Input::all();
        
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			return Response::json($output, 403);
        }
        
        $get = DB::table('provisional_tca_addresses')
                ->where('client_id', $input['client_id'])->where('address', $address)->first();
        
        if(!$get){
            $output['error'] = 'Provisional source address not found';
            return Response::json($output, 404);
        }
        
        $delete = DB::table('provisional_tca_addresses')->where('id', $get->id)->delete();
        
        if(!$delete){
            $output['error'] = 'Error deleting provisional source address';
            return Response::json($output, 500);
        }
        
        $output['result'] = true;
        return Response::json($output);
    }
    
    public function getProvisionalTCASourceAddressList()
    {
		$output = array();
		$output['result'] = false;
		$input = Input::all();
        
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			return Response::json($output, 403);
        }
        
        $list = DB::table('provisional_tca_addresses')->where('client_id', $input['client_id'])->get();
        
        $output['list'] = array();
        $output['result'] = true;
        if($list){
            foreach($list as $item){
                $output['list'][] = array('address' => $item->address, 'assets' => json_decode($item->assets, true));
            }
        }
        return Response::json($output);
    }

}

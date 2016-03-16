<?php
namespace TKAccounts\Http\Controllers\API;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\User, TKAccounts\Models\Address, TKAccounts\Models\UserMeta;
use TKAccounts\Providers\CMSAuth\CMSAccountLoader;
use TKAccounts\Models\OAuthClient as AuthClient;
use TKAccounts\Models\OAuthScope as Scope;
use DB, Exception, Response, Input, Hash;
use Illuminate\Http\JsonResponse;
use Tokenly\TCA\Access;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;
use TKAccounts\Repositories\ClientConnectionRepository;
use TKAccounts\Repositories\OAuthClientRepository;
use TKAccounts\Repositories\UserRepository;
use TKAccounts\Commands\ImportCMSAccount;
use TKAccounts\Commands\SendUserConfirmationEmail;
use TKAccounts\Commands\SyncCMSAccount;
use Illuminate\Foundation\Bus\DispatchesJobs;

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
			$balances = Address::getAllUserBalances($getUser->id, true);
			$output['result'] = $tca->checkAccess($full_stack, $balances);
		}
		return Response::json($output, $http_code);
	}
	
	public function getAddresses($username)
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
		
		
		$use_public = 1;
		if($priv_scope AND !isset($input['public'])){
			$use_public = null;
		}
		
		
		$address_list = Address::getAddressList($user->id, $use_public, 1, true);
		if(!$address_list OR count($address_list) == 0){
			$output['addresses'] = array();
		}
		else{
			$balances = array();
			foreach($address_list as $address){
				$balances[] = array('address' => $address->address, 'balances' => Address::getAddressBalances($address->id, true), 'public' => boolval($address->public));
			}
			$output['result'] = $balances;
		}
		return Response::json($output, $http_code);
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
}

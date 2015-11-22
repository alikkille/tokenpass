<?php
namespace TKAccounts\Http\Controllers\API;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\User, TKAccounts\Models\Address;
use TKAccounts\Providers\CMSAuth\CMSAccountLoader;
use DB, Exception, Response, Input;
use Illuminate\Http\JsonResponse;
use Tokenly\TCA\Access;

class APIController extends Controller
{

	public function checkTokenAccess($username)
	{
		$output = array();
		$http_code = 200;
		
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
			$input = Input::all();
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
			$balances = Address::getAllUserBalances($getUser->id);
			$output['result'] = $tca->checkAccess($full_stack, $balances);
		}
		return Response::json($output, $http_code);
	}
	
	public function getAddresses($username)
	{
		$output = array();
		$http_code = 200;
		
		$user = User::where('username', $username)->first();
		if(!$user){
			$http_code = 404;
			$output['result'] = false;
			$output['error'] = 'Username not found';
		}
		
		$address_list = Address::getAddressList($user->id, 1);
		if(!$address_list OR count($address_list) == 0){
			$output['addresses'] = array();
		}
		else{
			$balances = array();
			foreach($address_list as $address){
				$balances[] = array('address' => $address->address, 'balances' => Address::getAddressBalances($address->id));
			}
			$output['result'] = $balances;
		}
		return Response::json($output, $http_code);
	}
	
}

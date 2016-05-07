<?php

namespace TKAccounts\Http\Controllers\Inventory;

use Illuminate\Support\Facades\Auth;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\Address;
use TKAccounts\Models\UserMeta;
use Input, \Exception, Session, Response;

class InventoryController extends Controller
{

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
		$this->middleware('auth');
		$this->user = Auth::user();
    }


    public function index()
    {
		$addresses = Address::getAddressList($this->user->id, null, null);
		$balances = Address::getAllUserBalances($this->user->id);
		$disabled_tokens = Address::getDisabledTokens($this->user->id);
		
		$balance_addresses = array();
		foreach($addresses as $address){
			$bals = Address::getAddressBalances($address->id);
			if(!$bals OR count($bals) == 0){
				continue;
			}
			foreach($bals as $asset => $amnt){
				if($amnt <= 0){
					continue;
				}
				if(!isset($balance_addresses[$asset])){
					$balance_addresses[$asset] = array();
				}
				$balance_addresses[$asset][$address->address] = $amnt;
			}
		}
		
		return view('inventory.index', array(
			'addresses' => $addresses,
			'balances' => $balances,
			'balance_addresses' => $balance_addresses,
			'disabled_tokens' => $disabled_tokens,
		));
    }
    
    public function registerAddress()
    {
		//get input
		$input = Input::all();
		
		//check required fields
		if(!isset($input['address']) OR trim($input['address']) == ''){
			Session::flash('message', 'Bitcoin address required');
			Session::flash('message-class', 'alert-danger');
			return redirect('inventory');
		}
		
		//setup data
		$address = trim($input['address']);
		$label = '';
		if(isset($input['label'])){
			$label = trim(htmlentities($input['label']));
		}
		$public = 0;
		if(isset($input['public']) AND intval($input['public']) === 1){
			$public = 1;
		}
		
		//validate address
		$xchain = app('Tokenly\XChainClient\Client');
		$validate = $xchain->validateAddress($address);
		if(!$validate OR !$validate['result']){
			Session::flash('message', 'Please enter a valid bitcoin address');
			Session::flash('message-class', 'alert-danger');
			return redirect('inventory');
		}
		
		//check if they have this address registered already
		$user_addresses = Address::getAddressList($this->user->id, null, null);
		if($user_addresses AND count($user_addresses) > 0){
			$found = false;
			foreach($user_addresses as $addr){
				if($addr->address == $address){
					$found = true;
					break;
				}
			}
			if($found){
				Session::flash('message', 'Address has already been registered for this account');
				Session::flash('message-class', 'alert-danger');
				return redirect('inventory');
			}
		}
		
		//register address
		$new_address = app('TKAccounts\Repositories\AddressRepository')->create([
			'user_id'  => $this->user->id,
			'type'     => 'btc',
			'address'  => $address,
			'label'    => $label,
			'verified' => 0,
			'public'   => $public,
		]);
		$save = (!!$new_address);
		
		if(!$save){
			Session::flash('message', 'Error saving address');
			Session::flash('message-class', 'alert-danger');
			return redirect('inventory');
		}

		// sync with XChain
        $new_address->syncWithXChain();
		
		Session::flash('message', 'Bitcoin address registered!');
		Session::flash('message-class', 'alert-success');
		return redirect('inventory');
	}
	
	public function deleteAddress($address)
	{
		$get = Address::where('user_id', $this->user->id)->where('address', $address)->first();
		if(!$get){
			Session::flash('message', 'Address not found');
			Session::flash('message-class', 'alert-danger');
		}
		else{
			$delete = $get->delete();
			if(!$delete){
				Session::flash('message', 'Error deleting address '.$address);
				Session::flash('message-class', 'alert-danger');
			}
			else{
				Session::flash('message', 'Address '.$address.' deleted!');
				Session::flash('message-class', 'alert-success');
			}
		}
		return redirect('inventory');
	}
	
	public function editAddress($address)
	{
		$get = Address::where('user_id', $this->user->id)->where('address', $address)->first();
		if(!$get){
			Session::flash('message', 'Address not found');
			Session::flash('message-class', 'alert-danger');
		}
		else{
			
			$input = Input::all();
			
			if(isset($input['label'])){
				$get->label = trim(htmlentities($input['label']));
			}
			
			$public = 0;
			if(isset($input['public']) AND intval($input['public']) == 1){
				$public = 1;
			}
			$get->public = $public;
			
			$save = $get->save();
			
			if(!$save){
				Session::flash('message', 'Error updating address '.$address);
				Session::flash('message-class', 'alert-danger');
			}
			else{
				Session::flash('message', 'Address '.$address.' updated!');
				Session::flash('message-class', 'alert-success');
			}
		}
		return redirect('inventory');
	}
	
	public function verifyAddressOwnership($address)
	{
		$get = Address::where('user_id', $this->user->id)->where('address', $address)->first();
		if(!$get){
			Session::flash('message', 'Address not found');
			Session::flash('message-class', 'alert-danger');
		}
		else{
			
			$input = Input::all();
			
			if(!isset($input['sig']) OR trim($input['sig']) == ''){
				Session::flash('message', 'Signature required');
				Session::flash('message-class', 'alert-danger');	
			}
			else{
				$sig = $this->extract_signature($input['sig']);
				$xchain = app('Tokenly\XChainClient\Client');
				
				$verify_message = $xchain->verifyMessage($get->address, $sig, Address::getVerifyCode($get));
				$verified = false;
				if($verify_message AND $verify_message['result']){
					$verified = true;
				}
				
				if(!$verified){
					Session::flash('message', 'Signature for address '.$address.' is not valid');
					Session::flash('message-class', 'alert-danger');
				}
				else{
					$get->verified = 1;
					$save = $get->save();
					
					if(!$save){
						Session::flash('message', 'Error updating address '.$address);
						Session::flash('message-class', 'alert-danger');
					}
					else{
						//Address::updateUserBalances($this->user->id); //do a fresh inventory update (disabled for now, too slow)
						Session::flash('message', 'Address '.$address.' ownership proved successfully!');
						Session::flash('message-class', 'alert-success');
					}					
				}
			}
		}
		return redirect('inventory');
	}
	
	public function toggleAddress($address)
	{
		$output = array('result' => false);
		$response_code = 200;
		$get = Address::where('user_id', $this->user->id)->where('address', $address)->first();
		if(!$get){
			$output['error'] = 'Address not found';
			$response_code = 400;
		}
		else{
			$input = Input::all();
			if(!isset($input['toggle'])){
				$output['error'] = 'Toggle option required';
				$response_code = 400;
			}
			else{
				$toggle_val = $input['toggle'];
				if($toggle_val == 'true' OR $toggle_val === true){
					$toggle_val = 1;
				}
				else{
					$toggle_val = 0;
				}
				$get->active_toggle = $toggle_val;
				$save = $get->save();	
				if(!$save){
					$output['error'] = 'Error updating address';
					$response_code = 500;
				}
				else{
					$output['result'] = true;
				}
			}	
		}
		return Response::json($output, $response_code);
	}
	
	public function toggleAsset($asset)
	{
		$output = array('result' => false);
		$response_code = 200;

		$disabled_tokens = json_decode(UserMeta::getMeta($this->user->id, 'disabled_tokens'), true);
		if(!is_array($disabled_tokens)){
			$disabled_tokens = array();
		}
		
		$input = Input::all();
		if(!isset($input['toggle'])){
			$output['error'] = 'Toggle option required';
			$response_code = 400;
		}		
		else{
			$toggle_val = $input['toggle'];
			if($toggle_val == 'true' OR $toggle_val === true){
				$toggle_val = 1;
			}
			else{
				$toggle_val = 0;
			}		
			
			if($toggle_val == 1 AND in_array($asset, $disabled_tokens)){
				$k = array_search($asset, $disabled_tokens);
				unset($disabled_tokens[$k]);
				$disabled_tokens = array_values($disabled_tokens);
			}
			elseif($toggle_val == 0 AND !in_array($asset, $disabled_tokens)){
				$disabled_tokens[] = $asset;
			}
			$save = UserMeta::setMeta($this->user->id, 'disabled_tokens', json_encode($disabled_tokens));
			if(!$save){
				$output['error'] = 'Error updating list of disabled tokens';
				$response_code = 500;
			}
			else{
				$output['result'] = true;
			}
		}
		return Response::json($output, $response_code);
	}
	
	public function refreshBalances()
	{
		$update = Address::updateUserBalances($this->user->id);
		if(!$update){
			Session::flash('message', 'Error updating balances');
			Session::flash('message-class', 'alert-danger');
		}
		else{
			Session::flash('message', 'Token inventory balances updated!');
			Session::flash('message-class', 'alert-success');
		}
		return redirect('inventory');
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
    
    public function checkPageRefresh()
    {
        $output = array('result' => false);
        $user = Auth::user();
        if(!$user){
             return Response::json($output, 404);
        }
        
        $check_refresh = intval(UserMeta::getMeta($user->id, 'force_inventory_page_refresh'));
        if($check_refresh === 1){
            $output['result'] = true;
            UserMeta::setMeta($user->id, 'force_inventory_page_refresh', 0);
            $refresh_message = UserMeta::getMeta($user->id, 'inventory_refresh_message');
			Session::flash('message', $refresh_message);
			Session::flash('message-class', 'alert-success');
        }
        return Response::json($output);
    }

}

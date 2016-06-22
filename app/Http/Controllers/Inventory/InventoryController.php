<?php

namespace TKAccounts\Http\Controllers\Inventory;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Input, \Exception, Session, Response;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\Address;
use TKAccounts\Models\Provisional;
use TKAccounts\Models\UserMeta;

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

			$bals = Address::getAddressBalances($address->id, false, false);
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
				$balance_addresses[$asset][$address->address] = array('real' => $amnt, 'provisional' => array());
			}
            $promises = Provisional::getAddressPromises($address->address);
            foreach($promises as $promise){
				if(!isset($balance_addresses[$promise->asset])){
					$balance_addresses[$promise->asset] = array();
				}  
                if(!isset($balance_addresses[$promise->asset][$address->address])){
                    $balance_addresses[$promise->asset][$address->address] = array('real' => 0, 'provisional' => array());
                }
                $balance_addresses[$promise->asset][$address->address]['provisional'][] = $promise;
            }
		}

        $vars = [
            'addresses' => $addresses,
            'balances' => $balances,
            'balance_addresses' => $balance_addresses,
            'disabled_tokens' => $disabled_tokens];

		return view('inventory.index', $vars);
    }

    public function registerAddress()
    {
		//get input
		$input = Input::all();


		//check required fields
		if(!isset($input['address']) OR trim($input['address']) == ''){
            return $this->ajaxEnabledErrorResponse('Bitcoin address required', route('inventory.pockets'));
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
        try {
            $xchain = app('Tokenly\XChainClient\Client');
            $validate = $xchain->validateAddress($address);
        } catch (Exception $e) {
            return $this->ajaxEnabledErrorResponse($e->getMessage(), route('inventory.pockets'));
        }

		if(!$validate OR !$validate['result']){
            return $this->ajaxEnabledErrorResponse('Please enter a valid bitcoin address', route('inventory.pockets'));
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
                return $this->ajaxEnabledErrorResponse('Address has already been registered for this account', route('inventory.pockets'));
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
            return $this->ajaxEnabledErrorResponse('Error saving address', route('inventory.pockets'), 500);
		}

		// sync with XChain
        $new_address->syncWithXChain();

        return $this->ajaxEnabledSuccessResponse('Bitcoin address registered!', route('inventory.pockets'));
	}

	public function deleteAddress($address)
	{
		$get = Address::where('user_id', $this->user->id)->where('address', $address)->first();
		if(!$get){
            return $this->ajaxEnabledErrorResponse('Address not found', route('inventory.pockets'), 404);
		}
		else{
			$delete = $get->delete();
			if(!$delete){
                return $this->ajaxEnabledErrorResponse('Error updating address', route('inventory.pockets'), 500);
			}
			else{
                return $this->ajaxEnabledSuccessResponse('Address deleted!', route('inventory.pockets'));
			}
		}
	}

	public function editAddress($address)
	{
		$get = Address::where('user_id', $this->user->id)->where('address', $address)->first();
		if(!$get){
            return $this->ajaxEnabledErrorResponse('Address not found', route('inventory.pockets'), 404);
		}
		else{
			$input = Input::all();
            
			if(isset($input['label'])){
				$get->label = trim(htmlentities($input['label']));
			}
            
			$active = 0;
			if(isset($input['active']) AND intval($input['active']) == 1){
				$active = 1;
			}
			$get->active_toggle = $active;            

			$public = 0;
			if(isset($input['public']) AND intval($input['public']) == 1){
				$public = 1;
			}
			$get->public = $public;
            
            $login_toggle = 0;
            if(!$get->from_api AND isset($input['login']) AND intval($input['login']) == 1){
                $login_toggle = 1;
            }
            $get->login_toggle = $login_toggle;
            
            $second_factor = 0;
            if(!$get->from_api AND isset($input['second_factor']) AND intval($input['second_factor']) == 1){
                $second_factor = 1;
            }
            $get->second_factor_toggle = $second_factor;            

			$save = $get->save();

			if(!$save){
                return $this->ajaxEnabledErrorResponse('Error updating address', route('inventory.pockets'), 500);
			}
			else{
                return $this->ajaxEnabledSuccessResponse('Address updated!', route('inventory.pockets'));
			}
		}
	}

	public function verifyAddressOwnership($address)
	{
        $existing_addresses = Address::where('address', $address)->get();
        foreach($existing_addresses as $item) {
            if ($item->user_id != Auth::user()->id) {
                return $this->ajaxEnabledErrorResponse('The address '.$address.' is already in use by another account', route('inventory.pockets'), 400);
            }
        }

		$get = Address::where('user_id', $this->user->id)->where('address', $address)->first();

		if(!$get){
            return $this->ajaxEnabledErrorResponse('Address not found', route('inventory.pockets'), 404);
		}
		else{
			$input = Input::all();
			if(!isset($input['sig']) OR trim($input['sig']) == ''){
                return $this->ajaxEnabledErrorResponse('Signature required', route('inventory.pockets'), 400);
			}
			else{
				$sig = Address::extract_signature($input['sig']);
				$xchain = app('Tokenly\XChainClient\Client');
				$verify_message = $xchain->verifyMessage($get->address, $sig, Session::get($address));
				$verified = false;
				if($verify_message AND $verify_message['result']){
					$verified = true;
				}

				if(!$verified){
                    return $this->ajaxEnabledErrorResponse('Signature for address '.$address.' is not valid', route('inventory.pockets'), 400);
				}
				else{
					$get->verified = 1;
					$save = $get->save();

					if(!$save){
                        return $this->ajaxEnabledErrorResponse('Error updating address '.$address, route('inventory.pockets'), 400);
					}
					else{
						//Address::updateUserBalances($this->user->id); //do a fresh inventory update (disabled for now, too slow)
                        return $this->ajaxEnabledSuccessResponse('Address '.$address.' ownership proved successfully!', route('inventory.pockets'));
					}
				}
			}
		}
		return redirect(route('inventory.pockets'));
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

    public function toggleLogin($address)
    {
        $output = array('result' => false);
        $response_code = 200;
        $get = Address::where('user_id', $this->user->id)->where('address', $address)->first();
        $total_addresses = Address::getAddressList($this->user->id, null,1,1,1);
        if(count($total_addresses) > 4) {
            $output['error'] = 'Too many Addresses are used for Login, maximum of five.';
            $response_code = 400;
        }
        if(!$get){
            $output['error'] = 'Address not found';
            $response_code = 400;
        } else {
            if($get->from_api){
                $output['error'] = 'Cannot enable login for addresses added via API.';
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
                    $get->login_toggle = $toggle_val;
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
        }
        return Response::json($output, $response_code);
    }

	public function toggleSecondFactor()
	{
		$output = array('result' => false);
		$response_code = 200;
		$get = Address::where('user_id', $this->user->id)->where('address', $address)->first();

		if(!$get){
			$output['error'] = 'Address not found';
			$response_code = 400;
		}
		else {
            if($get->from_api){
                $output['error'] = 'Cannot enable 2FA for addresses added via API.';
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
                    $get->second_factor_toggle = $toggle_val;
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


    public function getPockets()
    {
		$addresses = Address::getAddressList($this->user->id, null, null);
		foreach($addresses as $address) {

			// Generate message for signing and flash for POST results
			if ($address->verified == 0) {
				$address['secure_code'] = Address::getSecureCodeGeneration();
				Session::flash($address->address, $address['secure_code']);
			}
		}

		return view('inventory.pockets', array(
			'addresses' => $addresses,
		));
    }

    // ------------------------------------------------------------------------
    protected function ajaxEnabledErrorResponse($error_message, $redirect_url, $error_code = 400) {
        if (Request::ajax()) {
            return Response::json(['success' => false, 'error' => $error_message], $error_code);
        }

        Session::flash('message', $error_message);
        Session::flash('message-class', 'alert-danger');
        return redirect($redirect_url);
    }

    protected function ajaxEnabledSuccessResponse($success_message, $redirect_url, $http_code = 200) {
        if (Request::ajax()) {
            return Response::json([
                'success'     => true,
                'message'     => $success_message,
                'redirectUrl' => $redirect_url,
            ], $http_code);
        }

        Session::flash('message', $success_message);
        Session::flash('message-class', 'alert-success');


        return redirect(route('inventory.pockets'));
    }

}

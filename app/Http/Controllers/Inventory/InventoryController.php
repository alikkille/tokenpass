<?php

namespace TKAccounts\Http\Controllers\Inventory;

use DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Input;
use Response;
use Session;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\Address;
use TKAccounts\Models\Provisional;
use TKAccounts\Models\User;
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
        $addresses = Address::getAddressList($this->user->id, null, true);
        foreach ($addresses as $address) {
            //remove some fields that the view doesnt need to know about
            unset($address->user_id);
            unset($address->xchain_address_id);
            unset($address->receive_monitor_id);
            unset($address->send_monitor_id);
        }
        $balances = Address::getAllUserBalances($this->user->id);
        ksort($balances);
        $disabled_tokens = Address::getDisabledTokens($this->user->id);
        $loans = Provisional::getUserOwnedPromises($this->user->id);
        $balance_addresses = [];
        $address_labels = [];
        if ($addresses) {
            foreach ($addresses as $address) {
                $bals = Address::getAddressBalances($address->id, false, false);
                if (!$bals or count($bals) == 0) {
                    continue;
                }

                foreach ($bals as $asset => $amnt) {
                    if ($amnt <= 0) {
                        continue;
                    }
                    if (!isset($balance_addresses[$asset])) {
                        $balance_addresses[$asset] = [];
                    }
                    $balance_addresses[$asset][$address->address] = ['real' => $amnt, 'provisional' => [], 'loans' => []];
                }
                $promises = Provisional::getAddressPromises($address->address);
                foreach ($promises as $promise) {
                    if (!isset($balance_addresses[$promise->asset])) {
                        $balance_addresses[$promise->asset] = [];
                    }
                    if (!isset($balance_addresses[$promise->asset][$address->address])) {
                        $balance_addresses[$promise->asset][$address->address] = ['real' => 0, 'provisional' => [], 'loans' => []];
                    }
                    $ref_data = $promise->getRefData();
                    if (isset($ref_data['show_as'])) {
                        if ($ref_data['show_as'] == 'username' and $promise->user_id > 0) {
                            $promise_user = User::find($promise->user_id);
                            if ($promise_user) {
                                $promise->source = $promise_user->username;
                            }
                        }
                    }
                    if (isset($ref_data['user'])) {
                        unset($ref_data['user']);
                    }
                    $promise->ref_data = $ref_data;
                    unset($promise->user_id);
                    unset($promise->ref);
                    $balance_addresses[$promise->asset][$address->address]['provisional'][] = $promise;
                }
                if ($loans) {
                    foreach ($loans as $loan) {
                        if ($loan->source == $address->address) {
                            if (!isset($balance_addresses[$loan->asset])) {
                                $balance_addresses[$loan->asset] = [];
                            }
                            if (!isset($balance_addresses[$loan->asset][$address->address])) {
                                $balance_addresses[$loan->asset][$address->address] = ['real' => 0, 'provisional' => [], 'loans' => []];
                            }
                            $ref_data = $loan->getRefData();
                            if (isset($ref_data['user'])) {
                                $get_user = User::find($ref_data['user']);
                                if ($get_user) {
                                    $loan->destination = $get_user->username;
                                }
                            }
                            $balance_addresses[$loan->asset][$address->address]['loans'][] = $loan;
                        }
                    }
                }
                $address_labels[$address->address] = trim($address->label);
            }
        }
        if ($loans) {
            foreach ($loans as $k => $loan) {
                if (isset($balances[$loan->asset])) {
                    $balances[$loan->asset] -= $loan->quantity;
                } else {
                    $balances[$loan->asset] = 0 - $loan->quantity;
                }
                $ref_data = $loan->getRefdata();
                if (isset($ref_data['user'])) {
                    unset($ref_data['user']);
                }
                if (!isset($ref_data['show_as'])) {
                    $ref_data['show_as'] = 'address';
                }
                $loans[$k]->ref_data = $ref_data;
                $loans[$k]->date = null;
                if ($loan->expiration > 0) {
                    $loans[$k]->date = date('Y-m-d', $loan->expiration);
                }
                $loans[$k]->show_as = 'username';
                if (isset($ref_data['show_as'])) {
                    $loans[$k]->show_as = $ref_data['show_as'];
                }
                unset($loans[$k]->ref);
                unset($loans[$k]->user_id);
            }
        }

        $vars = [
            'addresses'         => $addresses,
            'address_labels'    => $address_labels,
            'balances'          => $balances,
            'balance_addresses' => $balance_addresses,
            'disabled_tokens'   => $disabled_tokens,
            'loans'             => $loans,
            ];

        return view('inventory.index', $vars);
    }

    public function registerAddress()
    {
        //get input
        $input = Input::all();

        //check required fields
        if (!isset($input['address']) or trim($input['address']) == '') {
            return $this->ajaxEnabledErrorResponse('Bitcoin address required', route('inventory.pockets'));
        }

        //setup data
        $address = trim($input['address']);
        $label = '';
        if (isset($input['label'])) {
            $label = trim(htmlentities($input['label']));
        }
        $public = 0;
        if (isset($input['public']) and intval($input['public']) === 1) {
            $public = 1;
        }

        //validate address
        try {
            $xchain = app('Tokenly\XChainClient\Client');
            $validate = $xchain->validateAddress($address);
        } catch (Exception $e) {
            return $this->ajaxEnabledErrorResponse($e->getMessage(), route('inventory.pockets'));
        }

        if (!$validate or !$validate['result']) {
            return $this->ajaxEnabledErrorResponse('Please enter a valid bitcoin address', route('inventory.pockets'));
        }

        //check if they have this address registered already
        $user_addresses = Address::getAddressList($this->user->id, null, null);
        if ($user_addresses and count($user_addresses) > 0) {
            $found = false;
            foreach ($user_addresses as $addr) {
                if ($addr->address == $address) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
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
        $save = ((bool) $new_address);

        if (!$save) {
            return $this->ajaxEnabledErrorResponse('Error saving address', route('inventory.pockets'), 500);
        }

        // sync with XChain
        $new_address->syncWithXChain();

        return $this->ajaxEnabledSuccessResponse('Bitcoin address registered!', route('inventory.pockets'));
    }

    public function deleteAddress($address)
    {
        $get = Address::where('user_id', $this->user->id)->where('address', $address)->first();
        if (!$get) {
            return $this->ajaxEnabledErrorResponse('Address not found', route('inventory.pockets'), 404);
        } else {
            $delete = $get->delete();
            if (!$delete) {
                return $this->ajaxEnabledErrorResponse('Error updating address', route('inventory.pockets'), 500);
            } else {
                return $this->ajaxEnabledSuccessResponse('Address deleted!', route('inventory.pockets'));
            }
        }
    }

    public function editAddress($address)
    {
        $get = Address::where('user_id', $this->user->id)->where('address', $address)->first();

        if (!$get) {
            return $this->ajaxEnabledErrorResponse('Address not found', route('inventory.pockets'), 404);
        } else {
            $input = Input::all();

            if (isset($input['label'])) {
                $get->label = trim(htmlentities($input['label']));
            }

            $active = 0;
            if (isset($input['active']) and intval($input['active']) == 1) {
                $active = 1;
            }
            $get->active_toggle = $active;

            $public = 0;
            if (isset($input['public']) and intval($input['public']) == 1) {
                $public = 1;
            }
            $get->public = $public;

            $login_toggle = 0;
            if (!$get->from_api and isset($input['login']) and intval($input['login']) == 1 and $get->second_factor_toggle == 0) {
                $login_toggle = 1;
            }
            $get->login_toggle = $login_toggle;

            $second_factor = 0;
            if (!$get->from_api and isset($input['second_factor']) and intval($input['second_factor']) == 1 and $get->login_toggle == 0) {
                $second_factor = 1;
            }
            $get->second_factor_toggle = $second_factor;

            if (isset($input['notes'])) {
                $get->notes = trim(htmlentities($input['notes']));
            }

            $save = $get->save();

            if (!$save) {
                return $this->ajaxEnabledErrorResponse('Error updating address', route('inventory.pockets'), 500);
            } else {
                return $this->ajaxEnabledSuccessResponse('Address updated!', route('inventory.pockets'));
            }
        }
    }

    public function toggleAsset($asset)
    {
        $output = ['result' => false];
        $response_code = 200;

        $disabled_tokens = json_decode(UserMeta::getMeta($this->user->id, 'disabled_tokens'), true);
        if (!is_array($disabled_tokens)) {
            $disabled_tokens = [];
        }

        $input = Input::all();
        if (!isset($input['toggle'])) {
            $output['error'] = 'Toggle option required';
            $response_code = 400;
        } else {
            $toggle_val = $input['toggle'];
            if ($toggle_val == 'true' or $toggle_val === true) {
                $toggle_val = 1;
            } else {
                $toggle_val = 0;
            }

            if ($toggle_val == 1 and in_array($asset, $disabled_tokens)) {
                $k = array_search($asset, $disabled_tokens);
                unset($disabled_tokens[$k]);
                $disabled_tokens = array_values($disabled_tokens);
            } elseif ($toggle_val == 0 and !in_array($asset, $disabled_tokens)) {
                $disabled_tokens[] = $asset;
            }
            $save = UserMeta::setMeta($this->user->id, 'disabled_tokens', json_encode($disabled_tokens));
            if (!$save) {
                $output['error'] = 'Error updating list of disabled tokens';
                $response_code = 500;
            } else {
                $output['result'] = true;
            }
        }

        return Response::json($output, $response_code);
    }

    public function verifyAddressOwnership($address)
    {
        $existing_addresses = Address::where('address', $address)->get();
        foreach ($existing_addresses as $item) {
            if ($item->user_id != Auth::user()->id) {
                return $this->ajaxEnabledErrorResponse('The address '.$address.' is already in use by another account', route('inventory.pockets'), 400);
            }
        }

        $get = Address::where('user_id', $this->user->id)->where('address', $address)->first();

        if (!$get) {
            return $this->ajaxEnabledErrorResponse('Address not found', route('inventory.pockets'), 404);
        } else {
            $input = Input::all();
            if (isset($input['signature'])) {
                $input['sig'] = str_replace(' ', '+', urldecode($input['signature']));
            }
            if (!isset($input['sig']) or trim($input['sig']) == '') {
                return $this->ajaxEnabledErrorResponse('Signature required', route('inventory.pockets'), 400);
            } else {
                $sig = Address::extract_signature($input['sig']);
                $xchain = app('Tokenly\XChainClient\Client');
                $message = Session::get($address);
                Session::set($address, '');
                if (trim($message) == '') {
                    return $this->ajaxEnabledErrorResponse('Verification message not found', route('inventory.pockets'), 400);
                }
                $verify_message = $xchain->verifyMessage($get->address, $sig, $message);
                $verified = false;
                if ($verify_message and $verify_message['result']) {
                    $verified = true;
                }

                if (!$verified) {
                    return $this->ajaxEnabledErrorResponse('Signature for address '.$address.' is not valid', route('inventory.pockets'), 400);
                } else {
                    $get->verified = 1;
                    $save = $get->save();

                    if (!$save) {
                        return $this->ajaxEnabledErrorResponse('Error updating address '.$address, route('inventory.pockets'), 400);
                    } else {
                        //Address::updateUserBalances($this->user->id); //do a fresh inventory update (disabled for now, too slow)
                        return $this->ajaxEnabledSuccessResponse('Address '.$address.' ownership proved successfully!', route('inventory.pockets'));
                    }
                }
            }
        }

        return redirect(route('inventory.pockets'));
    }

    public function clickVerifyAddress($address)
    {
        return $this->verifyAddressOwnership($address);
    }

    public function refreshBalances()
    {
        $update = Address::updateUserBalances($this->user->id);
        if (!$update) {
            Session::flash('message', 'Error updating balances');
            Session::flash('message-class', 'alert-danger');
        } else {
            Session::flash('message', 'Token inventory balances updated!');
            Session::flash('message-class', 'alert-success');
        }

        return redirect('inventory');
    }

    public function checkPageRefresh()
    {
        $output = ['result' => false];
        $user = Auth::user();
        if (!$user) {
            return Response::json($output, 404);
        }

        $check_refresh = intval(UserMeta::getMeta($user->id, 'force_inventory_page_refresh'));
        if ($check_refresh === 1) {
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
        foreach ($addresses as $address) {

            // Generate message for signing and flash for POST results
            if ($address->verified == 0) {
                $address['secure_code'] = Address::getSecureCodeGeneration();
                Session::set($address->address, $address['secure_code']);
            }
            //remove some fields that the view doesnt need to know about
            unset($address->user_id);
            unset($address->xchain_address_id);
            unset($address->receive_monitor_id);
            unset($address->send_monitor_id);
        }

        return view('inventory.pockets', [
            'addresses' => $addresses,
        ]);
    }

    public function lendAsset($address, $asset)
    {
        //check valid verified address owned by user
        $user = Auth::user();
        $get_address = Address::where('address', $address)->where('verified', 1)->first();
        if (!$user or !$get_address or $get_address->user_id != $user->id) {
            return $this->ajaxEnabledErrorResponse('Address not found', route('inventory'), 404);
        }

        //get input
        $input = Input::all();

        //get quantity
        if (!isset($input['quantity'])) {
            return $this->ajaxEnabledErrorResponse('Quantity required', route('inventory'), 400);
        }
        $quantity = round(floatval($input['quantity']) * 100000000); //quantity in satoshis
        if ($quantity <= 0) {
            return $this->ajaxEnabledErrorResponse('Invalid quantity', route('inventory'), 400);
        }

        //get valid asset
        $asset_db = DB::table('address_balances')->where('address_id', $get_address->id)->where('asset', $asset)->first();
        if (!$asset_db) {
            return $this->ajaxEnabledErrorResponse('Invalid asset', route('inventory'), 400);
        }

        //get expiration
        $time = time();
        $expiration = null;
        if (trim($input['end_date']) != '') {
            if (isset($input['end_time'])) {
                $input['end_date'] .= ' '.$input['end_time'];
            }
            $expiration = strtotime($input['end_date']);
            if ($expiration <= $time) {
                return $this->ajaxEnabledErrorResponse('Expiration date must be sometime in the future', route('inventory'), 400);
            }
        }

        //get custom note
        $note = null;
        if (isset($input['note'])) {
            $note = trim(htmlentities($input['note']));
        }

        //get valid destination
        if (!isset($input['lendee']) or trim($input['lendee']) == '') {
            return $this->ajaxEnabledErrorResponse('Lendee required', route('inventory'), 400);
        }
        $destination = trim($input['lendee']);
        $ref = null;
        //check first if user, then if bitcoin address
        $get_user = User::where('username', $destination)->first();
        if ($get_user) {
            if ($get_user->id == $user->id) {
                return $this->ajaxEnabledErrorResponse('Cannot lend to self', route('inventory'), 400);
            }
            //use their first active verified address
            $first_address = Address::where('user_id', $get_user->id)->where('active_toggle', 1)->where('verified', 1)->first();
            if (!$first_address) {
                return $this->ajaxEnabledErrorResponse('Lendee does not have any verified addresses', route('inventory'), 400);
            }
            $destination = $first_address->address;
            $ref = 'user:'.$get_user->id;
        } else {
            //check if valid bitcoin address
            try {
                $xchain = app('Tokenly\XChainClient\Client');
                $validate_address = $xchain->validateAddress($destination);
            } catch (Exception $e) {
                return $this->ajaxEnabledErrorResponse($e->getMessage(), route('inventory'), 500);
            }
            if (!$validate_address or !$validate_address['result']) {
                return $this->ajaxEnabledErrorResponse('Please enter a valid bitcoin address', route('inventory'), 400);
            }

            $get_address = Address::where('address', $destination)->where('verified', 1)->first();
            if ($get_address) {
                $get_user = User::find($get_address->user_id);
            }
        }
        if ($destination == $address) {
            return $this->ajaxEnabledErrorResponse('Cannot lend to source address', route('inventory'), 400);
        }

        //decide if they want to reveeal source pocket address or show as username
        $show_as = null;
        if (isset($input['show_as'])) {
            switch ($input['show_as']) {
                case 'address':
                    $show_as = 'address';
                    break;
                case 'username':
                default:
                    $show_as = 'username';
                    break;
            }
            $add_ref = 'show_as:'.$show_as;
            if ($ref != null) {
                $ref .= ','.$add_ref;
            } else {
                $ref = $add_ref;
            }
        }

        //get total balance of all promises made including this one
        $total_promised = Provisional::getTotalPromised($address, $asset, $quantity);

        //check with crypto backend that the address really has enough tokens
        $valid_balance = false;

        try {
            $valid_balance = Provisional::checkValidPromisedAmount($address, $asset, $total_promised);
        } catch (Exception $e) {
            return $this->ajaxEnabledErrorResponse('Error validating promise balance: '.$e->getMessage(), route('inventory'), 500);
        }
        if (is_array($valid_balance) and !$valid_balance['valid']) {
            return $this->ajaxEnabledErrorResponse('Not enough real balance to lend this amount', route('inventory'), 500);
        } elseif (!$valid_balance) {
            return $this->ajaxEnabledErrorResponse('Unknown error validating promise balance', route('inventory'), 500);
        }

        //create the provisional/promise transaction
        $promise = new Provisional();
        $promise->source = $address;
        $promise->asset = $asset;
        $promise->destination = $destination;
        $promise->quantity = $quantity;
        $promise->expiration = $expiration;
        $promise->ref = $ref;
        $promise->user_id = $user->id;
        $promise->note = $note;

        $save = $promise->save();
        if (!$save) {
            return $this->ajaxEnabledErrorResponse('Error saving promise transaction', route('inventory'), 500);
        } else {
            if ($get_user) {
                $notify_data = ['promise' => $promise, 'lender' => $user, 'show_as' => $show_as];
                $get_user->notify('emails.loans.new-loan', 'New TCA loan for '.$promise->asset.' received '.date('Y/m/d'), $notify_data);
            }

            return $this->ajaxEnabledSuccessResponse($asset.' succesfully lent!', route('inventory'));
        }
    }

    public function deleteLoan($id)
    {
        $get = Provisional::find($id);
        $user = Auth::user();
        if (!$user or !$get or $get->user_id != $user->id) {
            return $this->ajaxEnabledErrorResponse('TCA loan not found', route('inventory'), 404);
        }
        $destination = $get->destination;
        $delete = $get->delete();
        if (!$delete) {
            return $this->ajaxEnabledErrorResponse('Error cancelling TCA loan', route('inventory'), 500);
        } else {
            $get_user = Address::where('address', $destination)->where('verified', 1)->first();
            if ($get_user) {
                $get_user = $get_user->user();
            }
            if ($get_user) {
                $notify_data = ['promise' => $get, 'lender' => $user];
                $get_user->notify('emails.loans.delete-loan', 'TCA loan for '.$get->asset.' cancelled '.date('Y/m/d'), $notify_data);
            }

            return $this->ajaxEnabledSuccessResponse('TCA loan cancelled', route('inventory'));
        }
    }

    public function editLoan($id)
    {
        $get = Provisional::find($id);
        $user = Auth::user();
        if (!$user or !$get or $get->user_id != $user->id) {
            return $this->ajaxEnabledErrorResponse('TCA loan not found', route('inventory'), 404);
        }
        $input = Input::all();

        $time = time();
        $expiration = null;
        if (trim($input['end_date']) != '') {
            if (isset($input['end_time'])) {
                $input['end_date'] .= ' '.$input['end_time'];
            }
            $expiration = strtotime($input['end_date']);
            if ($expiration <= $time) {
                return $this->ajaxEnabledErrorResponse('Expiration date must be sometime in the future', route('inventory'), 400);
            }
        }

        $ref_data = $get->getRefData();
        if (isset($input['show_as'])) {
            $show_as = null;
            switch ($input['show_as']) {
                case 'address':
                    $show_as = 'address';
                    break;
                case 'username':
                default:
                    $show_as = 'username';
                    break;
            }
            $ref_data['show_as'] = $show_as;
        }
        $join_ref = Provisional::joinRefData($ref_data);
        $old_expiration = $get->expiration;
        $get->expiration = $expiration;
        $get->ref = $join_ref;
        $get->updated_at = date('Y-m-d H:i:s');

        $save = $get->save();
        if (!$save) {
            return $this->ajaxEnabledErrorResponse('Error saving promise transaction', route('inventory'), 500);
        } else {
            if ($old_expiration != $expiration) {
                //send notification if expiration date has been changed
                $get_user = Address::where('address', $get->destination)->where('verified', 1)->first();
                if ($get_user) {
                    $get_user = $get_user->user();
                }
                if ($get_user) {
                    $notify_data = ['promise' => $get, 'lender' => $user, 'old_expiration' => $old_expiration];
                    $get_user->notify('emails.loans.edit-loan', 'TCA loan for '.$get->asset.' updated '.date('Y/m/d'), $notify_data);
                }
            }

            return $this->ajaxEnabledSuccessResponse('Loan successfully modified!', route('inventory'));
        }
    }

    // ------------------------------------------------------------------------
    protected function ajaxEnabledErrorResponse($error_message, $redirect_url, $error_code = 400)
    {
        if (Request::ajax()) {
            return Response::json(['success' => false, 'error' => $error_message], $error_code);
        }

        Session::flash('message', $error_message);
        Session::flash('message-class', 'alert-danger');

        return redirect($redirect_url);
    }

    protected function ajaxEnabledSuccessResponse($success_message, $redirect_url, $http_code = 200)
    {
        if (Request::ajax()) {
            return Response::json([
                'success'     => true,
                'message'     => $success_message,
                'redirectUrl' => $redirect_url,
            ], $http_code);
        }

        Session::flash('message', $success_message);
        Session::flash('message-class', 'alert-success');

        return redirect($redirect_url);
    }
}

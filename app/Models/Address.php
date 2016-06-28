<?php
namespace TKAccounts\Models;

use DB, Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use TKAccounts\Models\Address;
use Tokenly\CurrencyLib\CurrencyUtil;
use Tokenly\LaravelEventLog\Facade\EventLog;

class Address extends Model
{
    protected $table = 'coin_addresses';
    public $timestamps = true;

    // protected $fillable = ['user_id', 'type', 'address', 'label', 'verified', 'public', 'active_toggle', 'send_monitor_id', 'receive_monitor_id', 'xchain_address_id', ];
    protected static $unguarded = true;


    protected $casts = [
        'verified' => 'boolean',
        'public' => 'boolean',
        'from_api' => 'boolean',
        'active_toggle' => 'boolean',
        'login_toggle' => 'boolean',
        'second_factor_toggle' => 'boolean',
    ];

    
    public static function getAddressList($userId, $public = null, $active_toggle = 1, $verified_only = false, $login_toggle=null)
    {
        $get = Address::where('user_id', '=', $userId);
        if($verified_only){
            $get = $get->where('verified', 1);
        }
        if($public !== null){
            $get = $get->where('public', '=', intval($public));
        }
        if($active_toggle !== null){
            $get = $get->where('active_toggle', '=', intval($active_toggle));
        }
        if($login_toggle !== null){
            $get = $get->where('login_toggle', '=', intval($login_toggle));
        }
        return $get->orderBy('id', 'asc')->get();
    }
    
    public static function getAddressBalances($address_id, $filter_disabled = false, $and_provisional = true)
    {
        $address = Address::find($address_id);
        if(!$address OR $address->verified != 1 OR $address->active_toggle != 1){
            return false;
        }
        $balances = array();
        $get = DB::table('address_balances')->where('address_id', '=', $address->id)->get();
        if($get AND count($get) > 0){
            foreach($get as $row){
                $balances[$row->asset] = $row->balance;
            }
        }
        if($and_provisional){
            //add amounts from provisional txs
            $get_provisional = DB::table('provisional_tca_txs')->where('destination', $address->address)->get();
            if($get_provisional){
                foreach($get_provisional as $prov_tx){
                    if(isset($balances[$prov_tx->asset])){
                        $balances[$prov_tx->asset] += $prov_tx->quantity;
                    }
                    else{
                        $balances[$prov_tx->asset] = $prov_tx->quantity;
                    }
                }
            }
        }
        if($filter_disabled){
            $disabled = Address::getDisabledTokens($address->user_id);
            foreach($disabled as $asset){
                if(isset($balances[$asset])){
                    unset($balances[$asset]);
                }
            }
        }
        return $balances;
    }
    
    public static function updateAddressBalances($address_id, $balance_list)
    {
        $address = Address::find($address_id);
        if(!$address OR $address->verified != 1 OR $address->active_toggle != 1){
            return false;
        }

        return self::updateAddressBalancesTable($address, $balance_list);
    }


    public static function getAllUserBalances($user_id, $filter_disabled = false, $and_provisional = true)
    {
        $address_list = Address::getAddressList($user_id, null, true);
        if(!$address_list OR count($address_list) == 0){
            return array();
        }
        $balances = array();
        foreach($address_list as $address){
            $addr_balances = Address::getAddressBalances($address->id, false, $and_provisional);
            if(is_array($addr_balances)){
                foreach($addr_balances as $asset => $val){
                    if(!isset($balances[$asset])){
                        $balances[$asset] = intval($val);
                    }
                    else{
                        $balances[$asset] += intval($val);
                    }
                }
            }
        }
        if($filter_disabled){
            $disabled = Address::getDisabledTokens($user_id);
            foreach($disabled as $asset){
                if(isset($balances[$asset])){
                    unset($balances[$asset]);
                }
            }
        }
        return $balances;
    }

    public static function getInstantVerifyMessage($user)
    {
        $entropy = Address::getSecureCodeGeneration(8);
        $message = hash('sha256', $user->uuid . ' ' . $entropy);
        return $message;
    }

    public static function getUserVerificationCode($user, $type='readable')
    {
        $result = [];
        if(!$user){
            return false;
        }
        $sign_auth = UserMeta::getMeta($user->id,'sign_auth');
        if ($sign_auth == false) {
            Address::getVerificationType($type, $user);
            $sign_auth = UserMeta::getMeta($user->id, 'sign_auth');
        }
        if ($sign_auth != false) {
            $result['seconds'] = UserMeta::getDurationValueHasBeenSet($sign_auth);
            $result['extra'] = UserMeta::getMetaExtraValue($sign_auth);
        }
        if ($result['seconds'] > 3600 OR $result['extra'] == 'signed') {
            Address::getVerificationType($type, $user);
        }

        $result['user_meta'] = UserMeta::getMeta($user->id,'sign_auth');

        return $result;
    }

    private static function getVerificationType($type, $user=null) {
        switch ($type) {
            case 'complex':
                UserMeta::setMeta($user->id,'sign_auth',Address::getInstantVerifyMessage($user),0,0,'unsigned');
        break;
            case 'readable':
                UserMeta::setMeta($user->id,'sign_auth',Address::getSecureCodeGeneration() .' '. date('Y/m/d'),0,0,'unsigned');
        break;
            case 'complex readable':
                UserMeta::setMeta($user->id,'sign_auth',Address::getSecureCodeGeneration(8),0,0,'unsigned');
        break;
            case 'simple':
                UserMeta::setMeta($user->id,'sign_auth',Address::getSecureCodeGeneration(),0,0,'unsigned');
        break;
        }
    }

    public static function getSecureCodeGeneration($entropy=null, $language=null)
    {
        if(is_null($language)) {
            $file_content = file_get_contents(base_path() . "/database/wordlists/english.txt");
        } else {
            $file_content = file_get_contents(base_path() . '/database/wordlists/' . $language .'txt');
        }
        $dictionary = explode(PHP_EOL, $file_content);

        if(is_null($entropy)) {
            $one = random_int(0, 2047);
            $two = random_int(0, 2047);
            $code = random_int(0, 99);
        } else {
            $x = 0;
            $generation = [];
            while ($x < $entropy) {
                $generation[$x] = random_int(0, 2047);
                $x++;
            }

            $response = NULL;
            foreach ($generation as $item) {
                $response = $response . $dictionary[$item] . ' ' ;
            }

            return (string) trim($response);
        }
        $verify_prefix = Config::get('tokenpass.sig_verify_prefix');
        if($verify_prefix){
            $verify_prefix .= ' ';
        }
        return (string) $verify_prefix.$dictionary[$one]. ' ' .$dictionary[$two]. ' ' .$code;
    }
    
    public static function updateUserBalances($user_id)
    {
        $xchain = app('Tokenly\XChainClient\Client');

        $address_list = Address::where('user_id', $user_id)->where('verified', '=', 1)->get();
        if(!$address_list OR count($address_list) == 0){
            return false;
        }
        $stamp = date('Y-m-d H:i:s');
        foreach($address_list as $row){
            $balances = $xchain->getBalances($row->address, true);
            if($balances AND count($balances) > 0){
                $update = Address::updateAddressBalances($row->id, $balances);
                if(!$update){
                    return false;
                }
            }
        }
        return true;        
        
    }
    
    public static function getDisabledTokens($user_id)
    {
        $get = UserMeta::getMeta($user_id, 'disabled_tokens');
        $decode = json_decode($get, true);
        if(!$get OR !is_array($decode)){
            return array();
        }
        return $decode;
    }

    // ------------------------------------------------------------------------
    // XChain Sync
    
    public function syncWithXChain() {
        $xchain = app('Tokenly\XChainClient\Client');

        if (!$this['xchain_address_id']) {
            EventLog::log('xchain.addressSync', ['id' => $this['id'], 'address' => $this['address'],]);

            $update_vars = [];

            // create a xchain unmanaged address
            try {
                $result = $xchain->newUnmanagedPaymentAddress($this['address']);
                $xchain_address_id = $result['id'];
                $update_vars['xchain_address_id'] = $xchain_address_id;
            } catch (Exception $e) {
                EventLog::logError('xchain.addressSync.failed', $e, ['id' => $this['id'], 'address' => $this['address'], 'xchain_address_id' => $xchain_address_id,]);
                throw $e;
            }

            // create an xchain send monitor
            $webhook_endpoint = route('xchain.receive');
            if(env('XCHAIN_CALLBACK_USE_NONCE') == 'true'){
                $webhook_endpoint .= '?nonce='.env('XCHAIN_CALLBACK_NONCE');
            }

            try {
                $result = $xchain->newAddressMonitor($this['address'], $webhook_endpoint, 'send', true);
                $send_monitor_id = $result['id'];
                $update_vars['send_monitor_id'] = $send_monitor_id;
            } catch (Exception $e) {
                EventLog::logError('xchain.addressSync.failed', $e, ['id' => $this['id'], 'address' => $this['address'], 'send_monitor_id' => $send_monitor_id,]);
                throw $e;
            }

            // create an xchain receive monitor
            try {
                $result = $xchain->newAddressMonitor($this['address'], $webhook_endpoint, 'receive', true);
                $receive_monitor_id = $result['id'];
                $update_vars['receive_monitor_id'] = $receive_monitor_id;
            } catch (Exception $e) {
                EventLog::logError('xchain.addressSync.failed', $e, ['id' => $this['id'], 'address' => $this['address'], 'receive_monitor_id' => $receive_monitor_id,]);
                throw $e;
            }

            // update the address with the new data
            $address_repository = app('TKAccounts\Repositories\AddressRepository');
            $address_repository->update($this, $update_vars);
        }

        // always sync the balances with XChain, even if the address isn't new
        $this->syncAccountBalancesWithXChain();
    }

    public function syncAccountBalancesWithXChain() {
        $xchain = app('Tokenly\XChainClient\Client');
        $balances = $xchain->getAccountBalances($this['xchain_address_id'], 'default', 'confirmed');
        self::updateAddressBalancesTable($this, $this->balancesToSatoshis($balances));
    }

    // ------------------------------------------------------------------------
    
    protected function balancesToSatoshis($balances_float) {
        $balances_sat = [];
        foreach($balances_float as $asset => $float_balance) {
            $balances_sat[$asset] = CurrencyUtil::valueToSatoshis($float_balance);
        }
        return $balances_sat;
    }

    protected static function updateAddressBalancesTable($address, $balance_list) {

        $current = DB::table('address_balances')->where('address_id', '=', $address->id)->get();
        $stamp = date('Y-m-d H:i:s');
        foreach($balance_list as $asset => $balance){
            $found = false;
            foreach($current as $row){
                if($row->asset == $asset){
                    $found = $row;
                    break;
                }
            }
            if($found){
                //update balance entry
                DB::table('address_balances')->where('id', $found->id)->update(array('balance' => $balance, 'updated_at' => $stamp));
            }
            else{
                //new balance entry
                DB::Table('address_balances')->insert(array('address_id' => $address->id, 'asset' => $asset,
                                                            'balance' => $balance, 'updated_at' => $stamp));
            }
        }
        return true;
    }
    
    public function user()
    {
        return User::find($this->user_id);
    }

    public function balances()
    {
        $get = Address::getAddressBalances($this->id, false, false);
        if(!$get){
            return array();
        }
        return $get;
    }
    
    public function promises()
    {
        return Address::getPromiseBalances($this->id);
    }
    
    public function getPromiseBalances($addressId)
    {
        $address = Address::find($addressId);
        if(!$address){
            return false;
        }
        $get = DB::table('provisional_tca_txs')->where('destination', $address->address)->get();
        if(!$get){
            return array();
        }
        return $get;
    }
    
	public static function extract_signature($text,$start = '-----BEGIN BITCOIN SIGNATURE-----', $end = '-----END BITCOIN SIGNATURE-----')
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
    
    public static function checkUser2FAEnabled($user)
    {
        if($user->second_factor == 0){
            return false;
        }
        $count = Address::where('user_id', $user->id)->where('second_factor_toggle', 1)->where('verified', 1)->count();
        if(!$count OR $count == 0){
            return false;
        }
        return true;
    }

}


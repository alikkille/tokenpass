<?php
namespace TKAccounts\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use TKAccounts\Models\Address;
use Tokenly\CurrencyLib\CurrencyUtil;
use Tokenly\LaravelEventLog\Facade\EventLog;

class Address extends Model
{
    protected $table = 'coin_addresses';
    public $timestamps = true;

    protected $fillable = ['user_id', 'type', 'address', 'label', 'verified', 'public', 'active_toggle', 'send_monitor_id', 'receive_monitor_id', 'xchain_address_id', ];


    protected $casts = [
        'verified' => 'boolean',
        'public' => 'boolean',
    ];

    
    public static function getAddressList($userId, $public = null, $active_toggle = 1, $verified_only = false)
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
        return $get->orderBy('id', 'asc')->get();
    }
    
    public static function getAddressBalances($address_id, $filter_disabled = false)
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


    public static function getAllUserBalances($user_id, $filter_disabled = false)
    {
        $address_list = Address::getAddressList($user_id);
        if(!$address_list OR count($address_list) == 0){
            return array();
        }
        $balances = array();
        foreach($address_list as $address){
            $addr_balances = Address::getAddressBalances($address->id);
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
    
    public static function getVerifyCode($address)
    {
        return substr(hash('sha256', $address->address.':'.$address->user_id), 0, 10);
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

            // $table->string('xchain_address_id', 36)->unique()->nullable();
            // $table->string('receive_monitor_id', 36)->unique()->nullable();
            // $table->string('send_monitor_id', 36)->unique()->nullable();

            // create an xchain send monitor
            $webhook_endpoint = env('SITE_HOST').env('XCHAIN_CALLBACK_URL');
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
    }


<?php

namespace TKAccounts\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Address extends Model
{
	protected $table = 'coin_addresses';
	public $timestamps = true;
	
	public static function getAddressList($userId)
	{
		return Address::where('user_id', '=', $userId)->orderBy('id', 'asc')->get();
	}
	
	public static function getAddressBalances($address_id)
	{
		$address = Address::find($address_id);
		if(!$address){
			return false;
		}
		$balances = array();
		$get = DB::table('address_balances')->where('address_id', '=', $address->id)->get();
		if($get AND count($get) > 0){
			foreach($get as $row){
				$balances[$row->asset] = $row->balance;
			}
		}
		return $balances;
	}
	
	public static function updateAddressBalances($address_id, $balance_list)
	{
		$address = Address::find($address_id);
		if(!$address){
			return false;
		}
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

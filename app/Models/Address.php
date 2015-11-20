<?php

namespace TKAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
	protected $table = 'coin_addresses';
	public $timestamps = true;
	
	public static function getAddressList($userId)
	{
		return Address::where('user_id', '=', $userId)->orderBy('id', 'asc')->get();
	}
	
}

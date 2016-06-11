<?php
namespace TKAccounts\Models;
use Exception, DB;
use Illuminate\Database\Eloquent\Model;

class Provisional extends Model
{

    protected static $unguarded = true;

    protected $table = 'provisional_tca_txs';


    public static function getProofMessage($address, $client_id)
    {
        return $address.'_'.Provisional::getProofHash($client_id);
    }
    
    public static function getProofHash($client_id)
    {
        return hash('sha256', $client_id);
    }
    
    public static function getTotalPromised($address, $asset, $promise_total = 0, $ignore_id = false)
    {
        $other_promises = DB::table('provisional_tca_txs')
                        ->where('source', $address)
                        ->where('asset', $asset)
                        ->where('pseudo', 0)->get();
        if($other_promises){
            foreach($other_promises as $promise){
                if($promise->id == $ignore_id){
                    continue;
                }
                $promise_total += $promise->quantity;
            }
        }
        return $promise_total;
    }
    
    public static function checkValidPromisedAmount($address, $asset, $promise_total)
    {
        $xchain = app('Tokenly\XChainClient\Client');
        $balances = false;
        try{
            $balances = $xchain->getBalances($address, true);
        }
        catch(Exception $e){
            throw new Exception('Error checking source address '.$address.' balances');
        }
        
        if(!$balances){
            throw new Exception('Could not get balances for source address '.$address);
        }
        
        $valid_balance = false;
        if(isset($balances[$asset])){
            if($balances[$asset] >= $promise_total){
                $valid_balance = true;
            }
        }
        
        $output = array('valid' => $valid_balance, 'balance' => $balances[$asset]);
        return $output;
    }
    
    public static function getAddressPromises($address)
    {
        $get = DB::table('provisional_tca_txs')->where('destination', $address)->where('pseudo', 0)->get();
        return $get;
    }
    
    
    
}

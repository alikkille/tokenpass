<?php
namespace TKAccounts\Models;

class Provisional
{
    public static function getProofMessage($address, $client_id)
    {
        return $address.'_'.Provisional::getProofHash($client_id);
    }
    
    public static function getProofHash($client_id)
    {
        return hash('sha256', $client_id);
    }
    
    
}

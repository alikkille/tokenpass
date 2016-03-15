<?php

namespace TKAccounts\Console\Commands;

use Illuminate\Console\Command;
use TKAccounts\Models\Address;

class ScanCoinAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scanCoinAddresses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Looks at all registered cryptocurrency addresses and contacts XChain to update the local balance cache';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // The XChainClient service provider will build the client and apply xchain credentials
        $xchain = app('Tokenly\XChainClient\Client');

        $address_list = Address::where('verified', '=', 1)->get();
        if(!$address_list OR count($address_list) == 0){
			return false;
		}
		$stamp = date('Y-m-d H:i:s');
		$used_assets = array();
		foreach($address_list as $row){
			$balances = $xchain->getBalances($row->address, true);
			if($balances AND count($balances) > 0){
				foreach($balances as $asset => $amnt){
					if($amnt > 0){
						if(!isset($used_assets[$asset])){
							$used_assets[$asset] = $xchain->getAsset($asset);
						}
						if(!$used_assets[$asset]){
							continue;
						}					
						if(!$used_assets[$asset]['divisible']){
							$balances[$asset] = intval(round($amnt / 100000000));
						}
					}
				}
				$update = Address::updateAddressBalances($row->id, $balances);
				if(!$update){
					$this->error('Failed updating '.$row->address.' ['.$row->id.']');
				}
				else{
					$this->info('Updated '.$row->address.' ['.$row->id.']');
				}
			}
		}
		return true;
    }
}

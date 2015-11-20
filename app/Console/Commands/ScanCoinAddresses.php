<?php

namespace TKAccounts\Console\Commands;

use Illuminate\Console\Command;
use TKAccounts\Models\Address;
use Tokenly\XChainClient\Client;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->xchain = new Client(env('XCHAIN_CONNECTION_URL'), env('XCHAIN_API_TOKEN'), env('XCHAIN_API_KEY'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $address_list = Address::where('verified', '=', 1)->get();
        if(!$address_list OR count($address_list) == 0){
			return false;
		}
		$stamp = date('Y-m-d H:i:s');
		foreach($address_list as $row){
			$balances = $this->xchain->getBalances($row->address, true);
			if($balances AND count($balances) > 0){
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

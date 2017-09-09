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
        if (!$address_list or count($address_list) == 0) {
            return false;
        }
        $stamp = date('Y-m-d H:i:s');
        foreach ($address_list as $row) {
            $balances = $xchain->getBalances($row->address, true);
            if ($balances and count($balances) > 0) {
                $update = Address::updateAddressBalances($row->id, $balances);
                if (!$update) {
                    $this->error('Failed updating '.$row->address.' ['.$row->id.']');
                } else {
                    $this->info('Updated '.$row->address.' ['.$row->id.']');
                }
            }
        }

        return true;
    }
}

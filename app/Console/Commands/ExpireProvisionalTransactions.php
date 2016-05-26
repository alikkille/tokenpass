<?php

namespace TKAccounts\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ExpireProvisionalTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokenpass:expireProvisionalTransactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks all provisional transactions and expires where needed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time = time();
        $get = DB::table('provisional_tca_txs')->select('id', 'expiration')->get();
        if($get){
            $to_delete = array();
            foreach($get as $row){
                if($row->expiration == null){
                    continue;
                }
                if(intval($row->expiration) <= $time){
                    $to_delete[] = $row->id;
                }
            }
            if(count($to_delete) == 0){
                $this->info('Nothing to expire');
                return false;
            }
            $delete = DB::table('provisional_tca_txs')->whereIn('id', $to_delete)->delete();
            if(!$delete){
                $this->error('Error expiring provisional transactions');
                return false;
            }
            else{
                $this->info(count($to_delete).' provisional transactions expired');
                return true;
            }
        }
        else{
            $this->info('No provisional transactions');
            return false;
        }
    }
}

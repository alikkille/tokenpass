<?php

namespace TKAccounts\Console\Commands;

use DB;
use Illuminate\Console\Command;
use TKAccounts\Models\Address;
use TKAccounts\Models\Provisional;
use TKAccounts\Models\User;

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
        $get = Provisional::all();
        if ($get) {
            $to_delete = [];
            foreach ($get as $row) {
                if ($row->expiration == null or $row->expiration == 0) {
                    continue;
                }
                if (intval($row->expiration) <= $time) {
                    $to_delete[] = $row->id;
                    if ($row->user_id > 0) {
                        //send user notifications
                        $lender = User::find($row->user_id);
                        if ($lender) {
                            $lendee = Address::where('address', $row->destination)->where('verified', 1)->first();
                            if ($lendee) {
                                $lendee = $lendee->user();
                            }
                            $notify_data = ['promise' => $row, 'lender' => $lender, 'lendee' => $lendee];
                            //notify lender
                            $lender->notify('emails.loans.expire-lender', 'TCA loan for '.$row->asset.' expired '.date('Y/m/d'), $notify_data);
                            //notify lendee
                            if ($lendee) {
                                $lendee->notify('emails.loans.expire-lendee', 'TCA loan for '.$row->asset.' expired '.date('Y/m/d'), $notify_data);
                            }
                        }
                    }
                }
            }
            if (count($to_delete) == 0) {
                $this->info('Nothing to expire');

                return false;
            }
            $delete = DB::table('provisional_tca_txs')->whereIn('id', $to_delete)->delete();
            if (!$delete) {
                $this->error('Error expiring provisional transactions');

                return false;
            } else {
                $this->info(count($to_delete).' provisional transactions expired');

                return true;
            }
        } else {
            $this->info('No provisional transactions');

            return false;
        }
    }
}

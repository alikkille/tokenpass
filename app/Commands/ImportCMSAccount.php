<?php

namespace TKAccounts\Commands;

use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use TKAccounts\Commands\Command;
use TKAccounts\Commands\SendUserConfirmationEmail;
use TKAccounts\Commands\SyncCMSAccount;
use TKAccounts\Models\User;
use TKAccounts\Providers\CMSAuth\Util;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ImportCMSAccount extends Command implements SelfHandling
{

    use DispatchesJobs;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $user_repository = App('TKAccounts\Repositories\UserRepository');

        $existing_user = $user_repository->findBySlug(Util::slugify($this->username));
        if ($existing_user) { return false; }

        // load user info from CMS
        $loader = app('TKAccounts\Providers\CMSAuth\CMSAccountLoader');

        // get the full user info
        $cms_user_info = $loader->getFullUserInfoWithLogin($this->username, $this->password);
        if (!$cms_user_info) { return false; }

        // create a new user account
        $user_vars = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        // email
        if (isset($cms_user_info['email']) AND strlen($cms_user_info['email'])) {
            $user_vars['email'] = $cms_user_info['email'];
        }

        // real name
        if (isset($cms_user_info['profile']) AND $cms_user_info['profile'] AND isset($cms_user_info['profile']['real-name']) AND strlen($cms_user_info['profile']['real-name']['value'])) {
            $user_vars['name'] = $cms_user_info['profile']['real-name']['value'];
        }

        $new_user = $user_repository->create($user_vars);
        
        //attempt to load in any BTC addresses and other relevant account data they may have
        $this->dispatch(new SyncCMSAccount($new_user, array('username' => $this->username, 'password' => $this->password)));

        // and send welcome email
        $this->dispatch(new SendUserConfirmationEmail($new_user));

        return true;
    }



}

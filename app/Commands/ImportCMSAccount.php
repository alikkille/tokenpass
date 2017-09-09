<?php

namespace TKAccounts\Commands;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Foundation\Bus\DispatchesJobs;
use TKAccounts\Models\User;
use TKAccounts\Providers\CMSAuth\Util;

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
        if ($existing_user) {
            throw new \Exception('Invalid credentials');
        }

        // load user info from CMS
        $loader = app('TKAccounts\Providers\CMSAuth\CMSAccountLoader');

        // get the full user info
        $cms_user_info = $loader->getFullUserInfoWithLogin($this->username, $this->password);
        if (!$cms_user_info) {
            throw new \Exception('Invalid credentials');
        }

        // create a new user account
        $user_vars = [
            'username' => $this->username,
            'password' => $this->password,
            'email'    => '',
        ];

        // email
        if (isset($cms_user_info['email']) and strlen($cms_user_info['email'])) {
            $user_vars['email'] = $cms_user_info['email'];
        }

        if (trim($user_vars['email']) == '') {
            throw new \Exception('Email required');
        }

        $email_used = User::where('email', $user_vars['email'])->first();
        if ($email_used) {
            throw new \Exception('Email already in use');
        }

        // real name
        if (isset($cms_user_info['profile']) and $cms_user_info['profile'] and isset($cms_user_info['profile']['real-name']) and strlen($cms_user_info['profile']['real-name']['value'])) {
            $user_vars['name'] = $cms_user_info['profile']['real-name']['value'];
        }

        $new_user = $user_repository->create($user_vars);

        //attempt to load in any BTC addresses and other relevant account data they may have
        $this->dispatch(new SyncCMSAccount($new_user, ['username' => $this->username, 'password' => $this->password]));

        // and send welcome email
        $this->dispatch(new SendUserConfirmationEmail($new_user));

        return true;
    }
}

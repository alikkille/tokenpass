<?php

namespace TKAccounts\Providers\CMSAuth;

use Exception;
use Illuminate\Support\ServiceProvider;
use TKAccounts\Providers\CMSAuth\CMSAccountLoader;

class CMSAuthServiceProvider extends ServiceProvider {


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared('TKAccounts\Providers\CMSAuth\CMSAccountLoader', function ($app) {
            return new CMSAccountLoader(env('CMS_ACCOUNTS_HOST'), env('ENABLE_CMS_ACCOUNT_LOOKUPS', true));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['TKAccounts\Providers\CMSAuth\CMSAccountLoader'];
    }

}

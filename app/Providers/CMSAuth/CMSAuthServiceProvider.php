<?php

namespace TKAccounts\Providers\CMSAuth;

use Illuminate\Support\ServiceProvider;

class CMSAuthServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('TKAccounts\Providers\CMSAuth\CMSAccountLoader', function ($app) {
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

<?php

namespace TKAccounts\Http\Controllers\PlatformAdmin;

use TKAccounts\Models\OAuthClient;
use TKAccounts\Repositories\ProvisionalWhitelistRepository;
use Tokenly\PlatformAdmin\Controllers\ResourceController;

class PromiseWhitelistController extends ResourceController
{
    protected $view_prefix = 'whitelist';
    protected $repository_class = ProvisionalWhitelistRepository::class;

    public function __construct()
    {
        $this->middleware('sign');
    }

    protected function getValidationRules()
    {
        return [
            'address'   => 'required|max:255',
            'proof'     => '',
            'assets'    => '',
            'client_id' => 'exists:oauth_clients,id',
        ];
    }

    protected function modifyViewData_edit($view_data)
    {
        $view_data['clients'] = OAuthClient::all();
        if (!$view_data['clients']) {
            $view_data['clients'] = [];
        }

        return $view_data;
    }

    protected function modifyViewData_create($view_data)
    {
        $view_data['clients'] = OAuthClient::all();
        if (!$view_data['clients']) {
            $view_data['clients'] = [];
        }

        return $view_data;
    }
}

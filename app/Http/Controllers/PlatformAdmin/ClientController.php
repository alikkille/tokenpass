<?php

namespace TKAccounts\Http\Controllers\PlatformAdmin;

use TKAccounts\Repositories\OAuthClientRepository;
use Tokenly\PlatformAdmin\Controllers\ResourceController;

class ClientController extends ResourceController
{
    protected $view_prefix = 'client';
    protected $repository_class = OAuthClientRepository::class;

    public function __construct()
    {
        $this->middleware('sign');
    }

    protected function getValidationRules()
    {
        return [
            'name'     => 'required|max:255',
            'app_link' => 'url',
        ];
    }
}

<?php

namespace TKAccounts\Http\Controllers\PlatformAdmin;

use TKAccounts\Repositories\OAuthScopeRepository;
use Tokenly\PlatformAdmin\Controllers\ResourceController;

class ScopeController extends ResourceController
{
    protected $view_prefix = 'scope';
    protected $repository_class = OAuthScopeRepository::class;

    public function __construct()
    {
        $this->middleware('sign');
    }

    protected function getValidationRules()
    {
        return [
            'id'           => 'required|max:40',
            'description'  => 'max:255',
            'label'        => 'max:255',
            'notice_level' => 'numeric',
        ];
    }
}

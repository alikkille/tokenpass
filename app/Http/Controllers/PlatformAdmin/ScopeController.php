<?php

namespace TKAccounts\Http\Controllers\PlatformAdmin;

use Illuminate\Support\Facades\Log;
use TKAccounts\Repositories\OAuthScopeRepository;
use Tokenly\PlatformAdmin\Controllers\ResourceController;

class ScopeController extends ResourceController
{

    protected $view_prefix      = 'scope';
    protected $repository_class = OAuthScopeRepository::class;

    protected function getValidationRules() {
        return [
            'id' => 'required|max:40',
            'description' => 'max:255',
            'label' => 'max:255',
            'notice_level' => 'numeric',
        ];
    }    


}

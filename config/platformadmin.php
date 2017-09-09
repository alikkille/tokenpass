<?php

// sample
return [
    'routes' => [
        [
            'developmentMode' => true,
            'type'            => 'resource',
            'name'            => 'platform.admin.promise',
            'controller'      => TKAccounts\Http\Controllers\PlatformAdmin\PromisesController::class,
            'options'         => ['except' => 'show'],
        ],
        [
            'developmentMode' => true,
            'type'            => 'resource',
            'name'            => 'platform.admin.whitelist',
            'controller'      => TKAccounts\Http\Controllers\PlatformAdmin\PromiseWhitelistController::class,
            'options'         => ['except' => 'show'],
        ],
        [
            'type'            => 'resource',
            'name'            => 'platform.admin.connectedapps',
            'controller'      => TKAccounts\Http\Controllers\PlatformAdmin\ConnectedApplicationsController::class,
            'options'         => ['except' => 'show'],
        ],
        [
            'type'            => 'resource',
            'name'            => 'platform.admin.client',
            'controller'      => TKAccounts\Http\Controllers\PlatformAdmin\ClientController::class,
            'options'         => ['except' => 'show'],
        ],
        [
            'type'            => 'resource',
            'name'            => 'platform.admin.scopes',
            'controller'      => TKAccounts\Http\Controllers\PlatformAdmin\ScopeController::class,
            'options'         => ['except' => 'show'],
        ],
        [
            'type'            => 'resource',
            'name'            => 'platform.admin.address',
            'controller'      => TKAccounts\Http\Controllers\PlatformAdmin\AddressController::class,
            'options'         => ['except' => 'show'],
        ],
    ],

    'navigation' => [
        [
            'developmentMode' => true,
            'route'           => 'promise.index',
            'activePrefix'    => 'promise',
            'label'           => 'Promises',
        ],
        [
            'developmentMode' => true,
            'route'           => 'whitelist.index',
            'activePrefix'    => 'whitelist',
            'label'           => 'Promise Whitelist',
        ],
        [
            'route'        => 'client.index',
            'activePrefix' => 'client',
            'label'        => 'OAuth Clients',
        ],

        [
            'route'        => 'connectedapps.index',
            'activePrefix' => 'connectedapps',
            'label'        => 'Connected Apps',
        ],
        [
            'route'        => 'scopes.index',
            'activePrefix' => 'scopes',
            'label'        => 'OAuth Scopes',
        ],
        [
            'route'        => 'address.index',
            'activePrefix' => 'address',
            'label'        => 'Pocket Addresses',
        ],
    ],
];

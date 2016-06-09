<?php

// sample
return [
    'routes' => [
        [
            'developmentMode' => true,
            'type'            => 'resource',
            'name'            => 'platform.admin.promise',
            'controller'      => TKAccounts\Http\Controllers\PlatformAdmin\PromisesController::class,
            'options'         => ['except' => 'show',],
        ],
        [
            'type'            => 'resource',
            'name'            => 'platform.admin.connectedapps',
            'controller'      => TKAccounts\Http\Controllers\PlatformAdmin\ConnectedApplicationsController::class,
            'options'         => ['except' => 'show',],
        ],
        [
            'type'            => 'resource',
            'name'            => 'platform.admin.client',
            'controller'      => TKAccounts\Http\Controllers\PlatformAdmin\ClientController::class,
            'options'         => ['except' => 'show',],
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
            'route'        => 'client.index',
            'activePrefix' => 'client',
            'label'        => 'OAuth Clients',
        ],

        [
            'route'        => 'connectedapps.index',
            'activePrefix' => 'connectedapps',
            'label'        => 'Connected Apps',
        ],

    ],
];
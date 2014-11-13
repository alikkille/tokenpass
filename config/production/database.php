<?php

return [

	'default' => 'mysql',

	'connections' => [

        'mysql' => [
            'driver'    => 'mysql',
            'host'      => getenv('DB_PORT_3306_TCP_ADDR') ?: 'localhost',
            'port'      => getenv('DB_PORT_3306_TCP_PORT') ?: '3306',
            'database'  => 'tokenly_accounts_prod',
            'username'  => getenv('DB_USERNAME') ?: 'root',
            'password'  => getenv('DB_PASSWORD') ?: '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],

	],

];

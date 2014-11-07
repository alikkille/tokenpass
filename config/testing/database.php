<?php

return [

    'default' => 'sqlite',
	// 'default' => 'mysql',

	'connections' => [

		'sqlite' => [
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => '',
		],

        'mysql' => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'tokenly_accounts_test',
            'username'  => getenv('DB_USERNAME'),
            'password'  => getenv('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],

	],

];

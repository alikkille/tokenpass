<?php

namespace TKAccounts\TestHelpers;

use TKAccounts\Http\Kernel;

class TestKernel extends Kernel {

	/**
	 * The application's HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'TKAccounts\Http\Middleware\UnderMaintenance',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToRequest',
		// 'Illuminate\Session\Middleware\ReadSession',
		'Illuminate\Session\Middleware\WriteSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',

		// custom
        'TKAccounts\Http\Middleware\ReplaceTestVars',
	];


}

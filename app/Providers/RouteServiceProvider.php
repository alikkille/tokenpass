<?php namespace TKAccounts\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider {

	/**
	 * The controllers to scan for route annotations.
	 *
	 * @var array
	 */
	protected $scan = [
		'TKAccounts\Http\Controllers\HomeController',
		'TKAccounts\Http\Controllers\DashboardController',

		'TKAccounts\Http\Controllers\AuthController',
		'TKAccounts\Http\Controllers\PasswordController',

		'TKAccounts\Http\Controllers\OAuth\OAuthController',
	];

	/**
	 * All of the application's route middleware keys.
	 *
	 * @var array
	 */
	protected $middleware = [
		'auth' => 'TKAccounts\Http\Middleware\Authenticated',
		'auth.basic' => 'TKAccounts\Http\Middleware\AuthenticatedWithBasicAuth',
		'csrf' => 'TKAccounts\Http\Middleware\VerifyCsrfToken',
		'guest' => 'TKAccounts\Http\Middleware\IsGuest',
	];

	/**
	 * Called before routes are registered.
	 *
	 * Register any model bindings or pattern based filters.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function before(Router $router)
	{
		//
	}

	/**
	 * Define the routes for the application.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function map(Router $router)
	{
		$router->group(['namespace' => 'TKAccounts\Http\Controllers'], function($router)
		{
			require app_path('Http/routes.php');
		});
	}

}
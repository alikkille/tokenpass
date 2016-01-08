<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', [
    'as'   => 'welcome',
    'uses' => 'WelcomeController@index'
]);


// -------------------------------------------------------------------------
// User login and registration

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

// Update routes...
Route::get('auth/update', 'Auth\AuthController@getUpdate');
Route::post('auth/update', 'Auth\AuthController@postUpdate');

// Email confirmations...
Route::get('auth/sendemail', 'Auth\EmailConfirmationController@getSendEmail');
Route::post('auth/sendemail', 'Auth\EmailConfirmationController@postSendEmail');
Route::get('auth/verify/{token}', ['as' => 'auth.verify', 'uses' => 'Auth\EmailConfirmationController@verifyEmail']);

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');


// Connected apps routes...
Route::get('auth/connectedapps', 'Auth\ConnectedAppsController@getConnectedApps');
Route::get('auth/revokeapp/{clientid}', 'Auth\ConnectedAppsController@getRevokeAppForm');
Route::post('auth/revokeapp/{clientid}', 'Auth\ConnectedAppsController@postRevokeAppForm');

//token inventory management
Route::get('inventory', 'Inventory\InventoryController@index');
Route::post('inventory/address/new', 'Inventory\InventoryController@registerAddress');
Route::post('inventory/address/{address}/edit', 'Inventory\InventoryController@editAddress');
Route::post('inventory/address/{address}/verify', 'Inventory\InventoryController@verifyAddressOwnership');
Route::post('inventory/address/{address}/toggle', 'Inventory\InventoryController@toggleAddress');
Route::get('inventory/address/{address}/delete', 'Inventory\InventoryController@deleteAddress');
Route::get('inventory/refresh', 'Inventory\InventoryController@refreshBalances');
Route::post('inventory/asset/{asset}/toggle', 'Inventory\InventoryController@toggleAsset');

//client applications / API keys
Route::get('auth/apps', 'Auth\AppsController@index');
Route::post('auth/apps/new', 'Auth\AppsController@registerApp');
Route::post('auth/apps/{app}/edit', 'Auth\AppsController@updateApp');
Route::get('auth/apps/{app}/delete', 'Auth\AppsController@deleteApp');

// -------------------------------------------------------------------------
// Admin routes

Route::get('admin', ['middleware' => ['auth','admin',], function() {
    return view('admin.index');
}]);
Route::resource('admin/oauthclients', 'Admin\OAuthClientsController');
Route::resource('admin/oauthscopes', 'Admin\OAuthScopesController');


// -------------------------------------------------------------------------
// User routes

// User routes...
Route::get('dashboard', [
    'as'         => 'user.dashboard',
    'middleware' => 'auth',
    'uses'       => 'Accounts\DashboardController@getDashboard'
]);



// -------------------------------------------------------------------------
// oAuth routes

// oAuth authorization form...
Route::get('oauth/authorize', [
    'as'         => 'oauth.authorize.get',
    'middleware' => ['check-authorization-params', 'auth', 'csrf',],
    'uses'       => 'OAuth\OAuthController@getAuthorizeForm'
]);
Route::post('oauth/authorize', [
    'as'         => 'oauth.authorize.post',
    'middleware' => ['check-authorization-params', 'auth', 'csrf',],
    'uses'       => 'OAuth\OAuthController@postAuthorizeForm'
]);

// oAuth access token
Route::post('oauth/access-token', [
    'as'   => 'oauth.accesstoken',
    'uses' => 'OAuth\OAuthController@postAccessToken'
]);

// oAuth user
Route::get('oauth/user', [
    'as'         => 'oauth.user',
    'middleware' => ['oauth',],
    'uses'       => 'OAuth\OAuthController@getUser'
]);


// -------------------------------------------------------------------------
// API endpoints

Route::get('api/v1/tca/check/{username}', array('as' => 'api.tca.check', 'uses' => 'API\APIController@checkTokenAccess'));
Route::get('api/v1/tca/check-address/{address}', array('as' => 'api.tca.check-address', 'uses' => 'API\APIController@checkAddressTokenAccess'));
Route::get('api/v1/tca/addresses/{username}', array('as' => 'api.tca.addresses', 'uses' => 'API\APIController@getAddresses'));

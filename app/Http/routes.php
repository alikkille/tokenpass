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

// Send email confirmation...
Route::get('auth/sendemail', 'Auth\EmailConfirmationController@getSendEmail');
Route::post('auth/sendemail', 'Auth\EmailConfirmationController@postSendEmail');
Route::get('auth/verify/{token}', ['as' => 'auth.verify', 'uses' => 'Auth\EmailConfirmationController@verifyEmail']);


// -------------------------------------------------------------------------
// Admin routes

Route::get('admin', ['middleware' => 'auth', function() {
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


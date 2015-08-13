<?php

namespace TKAccounts\Http\Controllers\OAuth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Repositories\UserRepository;

/**
 */
class OAuthController extends Controller
{
    protected $authorizer;

    public function __construct()
    {

        // $this->beforeFilter('auth', ['only' => ['getAuthorize', 'postAuthorize']]);

        // $this->middleware('oauth2.error');
        // $this->middleware('oauth2.check-authorization-params', ['only' => ['getAuthorize', 'postAuthorize']]);

        // $this->beforeFilter('oauth', ['only' => ['getUser']]);

    }

    /**
     * Issue the Access Token
     * 
     * @return Response
     */
    public function postAccessToken()
    {
        Log::debug("postAccessToken called");
        try {
            return Response::json(Authorizer::issueAccessToken());
        } catch (\Exception $e) {
            Log::error("Exception: ".get_class($e).' '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Show the authorization form
     * 
     * @return Response
     */
    public function getAuthorizeForm()
    {
        $authParams = Authorizer::getAuthCodeRequestParams();
        $formParams = array_except($authParams,'client');
        $formParams['client_id'] = $authParams['client']->getId();
        return View::make('oauth.authorization-form', ['params'=>$formParams, 'client'=>$authParams['client'], 'scopes'=>$authParams['scopes']]);
    }

    /**
     * Process the authorization form
     * 
     * @return Response
     */
    public function postAuthorizeForm()
    {

        $params = Authorizer::getAuthCodeRequestParams();
        $params['user_id'] = Auth::user()->id;
        $redirect_uri = '';


        // if the user has allowed the client to access its data, redirect back to the client with an auth code
        if (Input::get('approve') !== null) {
            $redirect_uri = Authorizer::issueAuthCode('user', $params['user_id'], $params);
        }

        // if the user has denied the client to access its data, redirect back to the client with an error message
        if (Input::get('deny') !== null) {
            $redirect_uri = Authorizer::authCodeRequestDeniedRedirectUri();
        }

        return Redirect::to($redirect_uri);
    }

    /**
     * get the user
     * @GET("oauth/user", as="oauth.user")
     * @return Response
     */
    public function getUser(UserRepository $user_repository) {
        $owner_id = Authorizer::getResourceOwnerId();

        $user = $user_repository->findById($owner_id);
        // Log::info('getUser returning '.json_encode($user, 192));

        return [
            'id'       => $user['id'],
            'name'     => $user['name'],
            'username' => $user['username'],
            'email'    => $user['email'],
        ];
    }
}

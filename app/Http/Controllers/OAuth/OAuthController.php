<?php

namespace TKAccounts\Http\Controllers\OAuth;

use TKAccounts\Repositories\UserRepository;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use LucaDegasperi\OAuth2Server\Authorizer;

/**
 * @Middleware("csrf", except={"postAccessToken"})
 * @Middleware("auth", only={"getAuthorize","postAuthorize"})
 */
class OAuthController extends Controller
{
    protected $authorizer;

    public function __construct(Authorizer $authorizer, Guard $laravel_auth)
    {
        $this->authorizer = $authorizer;
        $this->laravel_auth = $laravel_auth;

        // $this->beforeFilter('auth', ['only' => ['getAuthorize', 'postAuthorize']]);

        $this->middleware('oauth2.error');
        $this->middleware('oauth2.check-authorization-params', ['only' => ['getAuthorize', 'postAuthorize']]);

        $this->beforeFilter('oauth', ['only' => ['getUser']]);

    }

    /**
     * Issue the Access Token
     * 
     * @Post("oauth/access-token", as="oauth.accesstoken")
     * 
     * @return Response
     */
    public function postAccessToken()
    {
        // Log::info('postAccessToken');
        try {
            return $this->authorizer->issueAccessToken();
        } catch (\Exception $e) {
            Log::error("Exception: ".get_class($e).' '.$e->getMessage());
            throw $e;
        }
         // return Response::json($this->authorizer->issueAccessToken());
    }

    /**
     * Show the authorization form
     * 
     * @Get("oauth/authorize", as="oauth.authorize")
     * 
     * @return Response
     */
    public function getAuthorize()
    {
        return View::make('oauth.authorize', $this->authorizer->getAuthCodeRequestParams());
    }

    /**
     * Process the authorization form
     * 
     * @Post("oauth/authorize", as="oauth.authorize")
     * 
     * @return Response
     */
    public function postAuthorize()
    {

        // get the user id
        $params['user_id'] = $this->laravel_auth->user()->id;

        $redirectUri = '';


        if (Input::get('approve') !== null) {
            $redirectUri = $this->authorizer->issueAuthCode('user', $params['user_id'], $params);
        }

        if (Input::get('deny') !== null) {
            $redirectUri = $this->authorizer->authCodeRequestDeniedRedirectUri();
        }

        return Redirect::to($redirectUri);
    }

    /**
     * get the user
     * @GET("oauth/user", as="oauth.user")
     * @return Response
     */
    public function getUser(Authorizer $authorizer, UserRepository $user_repository) {
        $owner_id = $authorizer->getResourceOwnerId();
        // Log::info('getUser \$owner_id='.json_encode($owner_id, 192));

        $user = $user_repository->findById($owner_id);
        // Log::info('getUser returning '.json_encode($user, 192));

        return [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
        ];
    }
}

<?php

namespace TKAccounts\Http\Controllers\Auth;

use Exception;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use TKAccounts\Commands\ImportCMSAccount;
use TKAccounts\Commands\SendUserConfirmationEmail;
use TKAccounts\Commands\SyncCMSAccount;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\User;
use TKAccounts\Models\UserMeta;
use TKAccounts\Providers\CMSAuth\Util;
use TKAccounts\Repositories\UserRepository;
use Validator;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use DispatchesJobs;
    use ThrottlesLogins;

    use AuthenticatesAndRegistersUsers {
        handleUserWasAuthenticated as trait_handleUserWasAuthenticated;
    }

    protected $username     = 'username';
    protected $redirectPath = '/dashboard';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;

        $this->middleware('guest', ['except' => ['getLogout','getUpdate','postUpdate']]);
        $this->middleware('auth', ['only' => ['getUpdate', 'postUpdate']]);

    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postRegister(Request $request)
    {
        $register_vars = $request->all();
        $register_vars['slug'] = Util::slugify(isset($register_vars['username']) ? $register_vars['username'] : '');

        $validator = $this->validator($register_vars);

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        // we can't create a new user with an existing LTB username
        $loader = app('TKAccounts\Providers\CMSAuth\CMSAccountLoader');
        if ($loader->usernameExists($register_vars['username'])) {
            $register_error = 'This username was found at LetsTalkBitcoin.com.  Please login with your existing credentials instead of creating a new account.';
            throw new HttpResponseException($this->buildFailedValidationResponse($request, ['username' => $register_error]));
        }


        $new_user = $this->create($request->all());
        Auth::login($new_user);

        // send the confirmation email
        $this->dispatch(new SendUserConfirmationEmail($new_user));

        // if we came from an authorization request
        //   then continue by redirecting the user to their original, intended request
        return redirect()->intended($this->redirectPath());
    }


    public function postLogin(Request $request, UserRepository $user_repository) {
        $this->validate($request, [
            $this->loginUsername() => 'required', 'password' => 'required',
        ]);

        if ($this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);
        list($login_error, $was_logged_in) = $this->performLoginLogic($credentials, $request->has('remember'));

        if ($was_logged_in) {
            return $this->handleUserWasAuthenticated($request, true);
        }

        // throttle
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    // ------------------------------------------------------------------------
    
    public function performLoginLogic($credentials, $remember) {
        $login_error = null;
        $second_time = false;
        while (true) {
            // try authenticating with our local database
            if (Auth::attempt($credentials, $remember)) {
                
                // sync BTC addresses from their LTB account where possible - temporary
                $this->syncCMSAccountData($credentials);

                $user = Auth::user();
                $session_id = \Session::getId();
                if ($user AND $session_id) {
                    UserMeta::setMeta($user->id, 'session_id', $session_id);
                }
                
                return [null, true];
            }

            if ($second_time) { break; }

            // never try to import a CMS user if the username exists in our database
            $existing_user = $this->user_repository->findBySlug(Util::slugify($this->username));
            if ($existing_user) { break; }

            // try importing a user with CMS credentials
            try{
                $imported_new_account = $this->importCMSAccount($credentials['username'], $credentials['password']);
            }
            catch(\Exception $e){
                $login_error = $e->getMessage();
                $imported_new_account = false;
            }
            if (!$imported_new_account) {
                break;
            }

            $second_time = true;
        }

        if ($login_error === null) { $login_error = $this->getFailedLoginMessage(); }

        return [$login_error, false];
    }

    // ------------------------------------------------------------------------
    

    public function getUpdate(Request $request)
    {
        $current_user = Auth::user();

        $flashable = [];
        foreach ($current_user->updateableFields() as $field_name) {
            $flashable[$field_name] = Session::hasOldInput($field_name) ? Session::getOldInput($field_name) : $current_user[$field_name];
        }
        $request->getSession()->flashInput($flashable);


        return view('auth.update', ['model' => $current_user]);
    }

    public function postUpdate(Request $request, UserRepository $user_repository)
    {
        try {
                
            $current_user = Auth::user();

            $request_params = $request->all();

            // if email or username is not present, then throw an error
            if (!isset($request_params['email']) OR !strlen($request_params['email'])) { throw new InvalidArgumentException("Email is requred", 1); }

            $update_vars = $request_params;

            // if submitted email matches current user email, then don't validate it
            if ($request_params['email'] == $current_user['email']) {
                unset($update_vars['email']);
            }

            // validate
            $validator = $this->updateValidator($update_vars);
            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }

            // check existing password
            $password_matched = $current_user->passwordMatches($request_params['password']);
            if (!$password_matched) {
                $error_text = 'Please provide the correct password to make changes.';
                Log::debug("\$request->input()=".json_encode($request->input(), 192));
                throw new HttpResponseException($this->buildFailedValidationResponse($request, ['password' => $error_text]));
            }


            // if a new password is present, set the password variable
            unset($update_vars['password']);
            if (isset($update_vars['new_password']) AND strlen($update_vars['new_password'])) {
                $update_vars['password'] = $update_vars['new_password'];
                unset($update_vars['new_password']);
            }
            unset($update_vars['new_password']);

            // filter for only valid variables
            $field_names = array_keys($validator->getRules());
            $filtered_update_vars = [];
            foreach($field_names as $field_name) {
                if (isset($update_vars[$field_name])) {
                    $filtered_update_vars[$field_name] = $update_vars[$field_name];
                }
            }
            $update_vars = $filtered_update_vars;


            // update the user
            $user_repository->update($current_user, $update_vars);

            // if the email changed, send a confirmation email
            if (isset($update_vars['email']) AND strlen($update_vars['email']) AND $update_vars['email'] != $current_user['confirmed_email']) {
                Log::debug("\$update_vars['email']=".json_encode($update_vars['email'], 192));
                $this->dispatch(new SendUserConfirmationEmail($current_user));
            }


            return redirect($this->redirectPath());

        } catch (InvalidArgumentException $e) {
            throw new HttpResponseException($this->buildFailedValidationResponse($request, [0 => $e->getMessage()]));
        }

    }

    protected function handleUserWasAuthenticated(Request $request, $throttles) {
        // if the email was not confirmed, require confirmation before continuing

        return $this->trait_handleUserWasAuthenticated($request, $throttles);
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => 'required|max:255',
            'username' => 'required|max:255|unique:users',
            'slug'     => 'required|max:255|unique:users',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function updateValidator(array $data)
    {
        return Validator::make($data, [
            'name'         => 'required|max:255',
            // 'username'     => 'sometimes|max:255|unique:users',
            'email'        => 'sometimes|email|max:255|unique:users',
            'new_password' => 'sometimes|confirmed|min:6',
            'password'     => 'required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        try {
            return $this->user_repository->create([
                'name'     => $data['name'],
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => $data['password'],
            ]);
        } catch (Exception $e) {
            throw $e;            
        }

    }

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // Desc
    
    protected function importCMSAccount($username, $password) {
        return $this->dispatch(new ImportCMSAccount($username, $password));
    }
    
    protected function syncCMSAccountData($credentials)
    {
		$user = Auth::user();
		if(!$user){
			return false;
		}
		return $this->dispatch(new SyncCMSAccount($user, $credentials));
	}
    
}

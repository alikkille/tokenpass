<?php

namespace TKAccounts\Http\Controllers\Auth;

use BitWasp\BitcoinLib\BitcoinLib;
use Exception;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use ReCaptcha;
use TKAccounts\Commands\ImportCMSAccount;
use TKAccounts\Commands\SendUserConfirmationEmail;
use TKAccounts\Commands\SyncCMSAccount;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\Address;
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

    protected $username = 'username';
    protected $redirectPath = '/dashboard';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;

        $this->middleware('guest', ['except' => ['getLogout', 'getUpdate', 'postUpdate', 'getSignRequirement', 'setSigned']]);
        $this->middleware('auth', ['only' => ['getUpdate', 'postUpdate']]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postRegister(Request $request)
    {
        if (env('APP_ENV') != 'testing') {
            $captcha = $this->checkCaptcha($request);
            if (is_null($captcha)) {
                return redirect()->back()->withErrors([$this->getGenericFailedMessage()]);
            }
            if ($captcha->isSuccess() == false) {
                return redirect()->back()->withErrors([$this->getGenericFailedMessage()]);
            }
        }

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

    private function checkCaptcha($request)
    {
        $secret = env('RECAPTCHA');
        $response = null;
        $reCaptcha = new \ReCaptcha\ReCaptcha($secret);

        if ($request['g-recaptcha-response']) {
            $response = $reCaptcha->verify(
                $request['g-recaptcha-response'],
                $_SERVER['REMOTE_ADDR']
            );
        }

        return $response;
    }

    public function postLogin(Request $request, UserRepository $user_repository)
    {
        $this->validate($request, [
            $this->loginUsername() => 'required', 'password' => 'required',
        ]);

        if ($this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);

        $user = DB::table('users')->where('users.username', '=', $credentials['username'])->first();

        try {
            if (Address::checkUser2FAEnabled($user)) {
                Session::set('user', $user);

                return redirect()->action('Auth\AuthController@getSignRequirement');
            }
        } catch (Exception $e) {
        }

        list($login_error, $was_logged_in) = $this->performLoginLogic($credentials, $request->has('remember'));

        if ($was_logged_in) {
            return $this->handleUserWasAuthenticated($request, true);
        }

        // throttle
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    // ------------------------------------------------------------------------

    public function performLoginLogic($credentials, $remember)
    {
        $login_error = null;
        $second_time = false;
        while (true) {
            // try authenticating with our local database
            if (Auth::attempt($credentials, $remember)) {

                // sync BTC addresses from their LTB account where possible - temporary
                $this->syncCMSAccountData($credentials);

                $user = Auth::user();
                $session_id = \Session::getId();
                if ($user and $session_id) {
                    UserMeta::setMeta($user->id, 'session_id', $session_id);
                }

                return [null, true];
            }

            if ($second_time) {
                break;
            }

            // never try to import a CMS user if the username exists in our database
            $existing_user = $this->user_repository->findBySlug(Util::slugify($this->username));
            if ($existing_user) {
                break;
            }

            // try importing a user with CMS credentials
            try {
                $imported_new_account = $this->importCMSAccount($credentials['username'], $credentials['password']);
            } catch (\Exception $e) {
                $login_error = $e->getMessage();
                $imported_new_account = false;
            }
            if (!$imported_new_account) {
                break;
            }

            $second_time = true;
        }

        if ($login_error === null) {
            $login_error = $this->getFailedLoginMessage();
        }

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
            if (!isset($request_params['email']) or !strlen($request_params['email'])) {
                throw new InvalidArgumentException('Email is requred', 1);
            }

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
                Log::debug('$request->input()='.json_encode($request->input(), 192));

                throw new HttpResponseException($this->buildFailedValidationResponse($request, ['password' => $error_text]));
            }

            // if a new password is present, set the password variable
            unset($update_vars['password']);
            if (isset($update_vars['new_password']) and strlen($update_vars['new_password'])) {
                $update_vars['password'] = $update_vars['new_password'];
                unset($update_vars['new_password']);
            }
            unset($update_vars['new_password']);

            // filter for only valid variables
            $field_names = array_keys($validator->getRules());
            $filtered_update_vars = [];
            foreach ($field_names as $field_name) {
                if (isset($update_vars[$field_name])) {
                    $filtered_update_vars[$field_name] = $update_vars[$field_name];
                }
            }
            $update_vars = $filtered_update_vars;

            // update the user
            $user_repository->update($current_user, $update_vars);

            // if the email changed, send a confirmation email
            if (isset($update_vars['email']) and strlen($update_vars['email']) and $update_vars['email'] != $current_user['confirmed_email']) {
                Log::debug("\$update_vars['email']=".json_encode($update_vars['email'], 192));
                $this->dispatch(new SendUserConfirmationEmail($current_user));
            }

            Session::flash('message', 'Settings updated!');
            Session::flash('message-class', 'alert-success');

            return redirect('/auth/update');
        } catch (InvalidArgumentException $e) {
            throw new HttpResponseException($this->buildFailedValidationResponse($request, [0 => $e->getMessage()]));
        }
    }

    public function toggleSecondFactor()
    {
        $output = ['result' => false];
        $response_code = 200;
        $total_addresses = Address::getAddressList($this->user->id, null, 1, 1, 1);

        $second_factor_addresses = [];
        foreach ($total_addresses as $address) {
            if ($address->second_factor_toggle) {
                array_push($second_factor_addresses, $address);
            }
        }

        if (empty($second_factor_addresses)) {
            $output['error'] = 'Please allow at minimum one address before switching on Second Factor Authentication';
            $response_code = 400;
        }

        $input = Input::all();
        if (!isset($input['toggle'])) {
            $output['error'] = 'Toggle option required';
            $response_code = 400;
        } else {
            $toggle_val = $input['toggle'];
            if ($toggle_val == 'true' or $toggle_val === true) {
                $toggle_val = 1;
            } else {
                $toggle_val = 0;
            }
            $get->second_factor_toggle = $toggle_val;
            $save = $get->save();
            if (!$save) {
                $output['error'] = 'Error updating address';
                $response_code = 500;
            } else {
                $output['result'] = true;
            }
        }

        return Response::json($output, $response_code);
    }

    public function getSignRequirement(Request $request, $user = null)
    {
        $sig = Input::get('signature');
        if (trim($sig) != '') {
            $request->request->set('signed_message', str_replace(' ', '+', urldecode($sig)));

            return $this->setSigned($request);
        }
        if (session()->has('user')) {
            $user = session()->get('user');
            $request->session()->reflash();
        } else {
            $user = Auth::user();
        }
        if (!$user) {
            return redirect('auth/login');
        }
        $secondauth_enabled = Address::checkUser2FAEnabled($user);
        if (!$secondauth_enabled) {
            return redirect('auth/login');
        }
        $sigval = Address::getUserVerificationCode($user, 'simple');

        return view('auth.sign', ['sigval' => $sigval['user_meta'], 'redirect' => $request['redirect']]);
    }

    public function setSigned(Request $request)
    {
        if (session()->has('user')) {
            $user = session()->get('user');
        } else {
            $user = Auth::user();
        }

        if (!$user) {
            return redirect('auth/login');
        }

        Session::set('user', null);

        //check if they actually have 2fa enabled
        $secondauth_enabled = Address::checkUser2FAEnabled($user);
        if (!$secondauth_enabled) {
            return redirect()->route('auth.login')->withErrors([$this->getFailedLoginMessage()]);
        }

        $sigval = Address::getUserVerificationCode($user, 'simple');
        $sig = Address::extract_signature($request->request->get('signed_message'));

        try {
            $address = BitcoinLib::deriveAddressFromSignature($sig, $sigval['user_meta']);
        } catch (Exception $e) {
            return redirect()->route('auth.login')->withErrors([$this->getFailedLoginMessage()]);
        }

        //check if this address belongs to the user and they have 2FA enabled
        $get_address = Address::where('address', $address)->first();
        if (!$get_address or $get_address->user_id != $user->id or $get_address->second_factor_toggle == 0) {
            return redirect()->route('auth.login')->withErrors([$this->getFailedLoginMessage()]);
        }

        //verify signed message on xchain
        $verify = $this->verifySignature(['address' => $address, 'sig' => $sig, 'sigval' =>  $sigval['user_meta']]);
        if ($verify) {
            UserMeta::setMeta($user->id, 'sign_auth', $sigval['user_meta'], 0, 0, 'signed');
            if (empty($request['redirect'])) {
                Auth::loginUsingId($user->id);

                return $this->handleUserWasAuthenticated($request, true);
            }

            return redirect(urldecode($request['redirect']));
        } else {
            return redirect()->route('auth.login')->withErrors([$this->getFailedLoginMessage()]);
        }
    }

    public function getBitcoinLogin(Request $request)
    {

    // Generate message for signing and flash for POST results
        if (Input::get('signature')) {
            $request->request->set('signed_message', Input::get('signature'));

            return $this->postBitcoinLogin($request);
        }
        $sigval = Address::getSecureCodeGeneration();
        Session::flash('sigval', $sigval);

        return view('auth.bitcoin', ['sigval' => $sigval]);
    }

    public function postBitcoinLogin(Request $request)
    {
        $sigval = Session::get('sigval');
        $sig = Address::extract_signature($request->request->get('signed_message'));

        if ($sigval == null) {
            return redirect()->route('auth.bitcoin')->withErrors([$this->getFailedLoginMessage()]);
        }

        try {
            $address = BitcoinLib::deriveAddressFromSignature($sig, $sigval);
        } catch (Exception $e) {
            return redirect()->route('auth.bitcoin')->withErrors([$this->getFailedLoginMessage()]);
        }

        $data = [
        'sigval'  => $sigval,
        'address' => $address,
        'sig'     => $sig, ];

        if ($this->verifySignature($data)) {
            try {
                $result = User::getByVerifiedAddress($address);
            } catch (Exception $e) {
                return redirect()->route('auth.bitcoin')->withErrors([$this->getFailedLoginMessage(),
            ]);
            }
        }
        if (isset($result) && !false) {
            try {
                $user = User::find($result->user_id);
                if (Address::checkUser2FAEnabled($user)) {
                    Session::flash('user', $user);

                    return redirect()->action('Auth\AuthController@getSignRequirement');
                }
            } catch (Exception $e) {
            }

            try {
                Auth::loginUsingId($result->user_id);
            } catch (Exception $e) {
                return redirect()->route('auth.bitcoin')->withErrors([$this->getFailedLoginMessage()]);
            }

            return $this->handleUserWasAuthenticated($request, true);
        } else {
            return redirect()->route('auth.bitcoin')->withErrors([$this->getFailedLoginMessage(),
        ]);
        }
    }

    protected function verifySignature($data)
    {
        $sig = Address::extract_signature($data['sig']);
        $xchain = app('Tokenly\XChainClient\Client');

        $verify_message = $xchain->verifyMessage($data['address'], $sig, $data['sigval']);
        if ($verify_message and $verify_message['result']) {
            return true;
        } else {
            return false;
        }
    }

    protected function handleUserWasAuthenticated(Request $request, $throttles)
    {
        // if the email was not confirmed, require confirmation before continuing

        return $this->trait_handleUserWasAuthenticated($request, $throttles);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
        'name'     => 'max:255',
        'username' => 'required|max:255|unique:users',
        'slug'     => 'sometimes|max:255|unique:users',
        'email'    => 'required|email|max:255|unique:users',
        'password' => 'required|confirmed|min:6',
    ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function updateValidator(array $data)
    {
        return Validator::make($data, [
        'name'         => 'max:255',
        // 'username'     => 'sometimes|max:255|unique:users',
        'email'             => 'sometimes|email|max:255|unique:users',
        'new_password'      => 'sometimes|confirmed|min:6',
        'password'          => 'required',
        'second_factor'     => 'integer',
    ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
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

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getGenericFailedMessage()
    {
        return Lang::has('auth.generic.fail')
        ? Lang::get('auth.generic.fail')
        : 'There has been an error, please check your input.';
    }

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // Desc

    protected function importCMSAccount($username, $password)
    {
        return $this->dispatch(new ImportCMSAccount($username, $password));
    }

    protected function syncCMSAccountData($credentials)
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $this->dispatch(new SyncCMSAccount($user, $credentials));
    }
}

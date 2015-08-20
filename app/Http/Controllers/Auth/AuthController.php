<?php

namespace TKAccounts\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\User;
use TKAccounts\Repositories\UserRepository;
use Validator;
use Exception;
use InvalidArgumentException;

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

    use ThrottlesLogins;

    use AuthenticatesUsers, RegistersUsers {
        AuthenticatesUsers::redirectPath insteadof RegistersUsers;
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


    public function getUpdate(Request $request)
    {
        $current_user = Auth::user();

        $flashable = [];
        foreach ($current_user->updateableFields() as $field_name) {
            $flashable[$field_name] = $current_user[$field_name];
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
                throw new InvalidArgumentException("Please provide the correct password to make changes.", 1);
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
}

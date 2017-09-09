<?php

namespace TKAccounts\Http\Controllers\Auth;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use InvalidArgumentException;
use TKAccounts\Commands\SendUserConfirmationEmail;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\User;
use TKAccounts\Repositories\UserRepository;

class EmailConfirmationController extends Controller
{
    use DispatchesJobs;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;

        $this->middleware('auth', ['except' => 'verifyEmail']);
    }

    public function getSendEmail(Request $request)
    {
        $current_user = Auth::user();

        return view('auth.sendemail', ['model' => $current_user]);
    }

    public function postSendEmail(Request $request, UserRepository $user_repository)
    {
        $current_user = Auth::user();

        // send the email
        $this->dispatch(new SendUserConfirmationEmail($current_user));

        // update view
        return view('auth.sendemailcomplete', ['model' => $current_user]);
    }

    public function verifyEmail($token)
    {
        $errors = new MessageBag();

        try {
            DB::transaction(function () use ($token) {
                $user = $this->user_repository->findByConfirmationCode($token);
                if (!$user) {
                    throw new InvalidArgumentException('This email confirmation link has already been used or was not found.', 1);
                }

                if (Carbon::now()->gt($user['confirmation_code_expires_at'])) {
                    throw new InvalidArgumentException('This email confirmation link has expired.  Please send a new confirmation email.', 1);
                }

                $this->user_repository->update($user, [
                    'confirmed_email'              => $user['email'],
                    'confirmation_code'            => null,
                    'confirmation_code_expires_at' => null,
                ]);
            });
        } catch (InvalidArgumentException $e) {
            $errors = new MessageBag(['error' => $e->getMessage()]);
            // $errors->put('error', $e->getMessage());
        }

        return view('auth.sendemailverified', ['errors' => $errors]);
    }
}

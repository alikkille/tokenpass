<?php

namespace TKAccounts\Commands;

use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Mail;
use TKAccounts\Models\User;

class SendUserConfirmationEmail extends Command implements SelfHandling
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $confirmation_code = '';

        $this->resetEmailConfirmationCode();

        Mail::send('emails.activate', ['user' => $this->user, 'token' => $this->user['confirmation_code']], function ($message) {
            $message->to($this->user['email'], $this->user['name'])->subject('Verify Your Tokenly Account');
        });
    }

    protected function resetEmailConfirmationCode()
    {
        $user_repository = App('TKAccounts\Repositories\UserRepository');
        $token_generator = App('Tokenly\TokenGenerator\TokenGenerator');

        $user_repository->update($this->user, [
            'confirmation_code'            => $token_generator->generateToken(30, 'E'),
            'confirmation_code_expires_at' => Carbon::now()->modify('+12 hours'),
        ]);
    }
}

<?php

namespace TKAccounts\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user) {
            // go to dashboard
            return redirect(route('user.dashboard'));
        }

        return view('public-index');
    }
}

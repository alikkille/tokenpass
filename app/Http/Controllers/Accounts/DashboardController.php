<?php

namespace TKAccounts\Http\Controllers\Accounts;

use Illuminate\Support\Facades\Auth;
use TKAccounts\Http\Controllers\Controller;

class DashboardController extends Controller
{

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    public function getDashboard() {
        return view('accounts/dashboard', [
            'user' => Auth::user(),
        ]);
    }

}

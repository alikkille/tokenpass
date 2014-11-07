<?php namespace TKAccounts\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller {

	/**
	 * @Get("/")
	 */
	public function index()
	{
		return view('home', ['currentUser' => Auth::user()]);
	}

}

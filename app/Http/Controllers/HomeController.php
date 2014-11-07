<?php namespace TKAccounts\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends BaseController {

	/**
	 * @Get("/")
	 */
	public function index()
	{
		return view('home', ['currentUser' => Auth::user()]);
	}

}

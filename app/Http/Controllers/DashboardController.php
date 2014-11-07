<?php namespace TKAccounts\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

/**
 * @Middleware("auth")
 */
class DashboardController extends BaseController {

	/**
	 * Display the dashboard
	 *
	 * @Get("/user/dashboard", as="user.dashboard")
	 * 
	 * @return Response
	 */
	public function index()
	{

		return view('dashboard.dashboard', ['currentUser' => Auth::user()]);
	}


}

@extends('layouts.one-column')

@section('htmlTitle', 'Home')

@section('pageTitle', 'Welcome to Tokenly Accounts')


@section('bodyContent')
	<div class="welcome">
        @if ($currentUser)
        <div>
            
            <p>Welcome back, {{$currentUser->username}}.</p>
            
        </div>
        <div>
            <a class="small button" href="/user/dashboard">Go To Your Dashboard</a>
            <a class="small button" href="/auth/logout">Logout</a>
        </div>
        @else
        <p>It looks like you are new here or haven't signed in for a while.</p>
        <div>
            <a class="small button" href="/auth/register">Sign Up</a>
            <a class="small button" href="/auth/login">Login</a>
        </div>
        @endif


	</div>
@stop

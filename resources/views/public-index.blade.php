@extends('layouts.guest')


@section('body_content')

<h1>Welcome to Tokenpass</h1>

<div class="spacer1"></div>

<p>Tokenpass is where you register and login for Tokenly services such as Swapbot.</p>


<div class="spacer1"></div>

<p>If you have a <a href="https://letstalkbitcoin.com">Let's Talk Bitcoin</a> account with, then you can create a Tokenly Account immediately by logging in with your existing login and password.</p>
<a href="/auth/login" class="btn btn-primary">Login</a>


<div class="spacer2"></div>

<p>You can also register and create a new Tokenly Account.</p>

<a href="/auth/register" class="btn btn-success">Create a New Account</a>

@endsection

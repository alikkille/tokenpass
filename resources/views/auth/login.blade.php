@extends('layouts.guest')

@section('htmltitle', 'Login')

@section('body_class', 'login')

@section('body_content')

<div class="everything">
	<div class="logo"><a href="/">token<strong>pass</strong></a></div>
		<div class="form-wrapper">
			@include('partials.errors', ['errors' => $errors])
			<form method="POST" action="/auth/login">
				{!! csrf_field() !!}
				<input class="with-forgot" id="Username" name="username" type="text" placeholder="username" value="{{ old('username') }}">
				<input class="with-forgot" id="Password" name="password" type="password" placeholder="password">
				<button type="submit" class="login-btn">Login</button>
			</form>
		</div>
		<div class="register-subtext">
			<span>Don't have an account? <a href="/auth/register">Register</a></span>
		</div>
		<div class="or-divider-module">
			<div class="divider">.</div><span class="or">or</span>
			<div class="divider">.</div>
		</div><a class="signin-with-btc-btn" href="/auth/bitcoin">Sign In With Bitcoin</a>
</div>

@endsection

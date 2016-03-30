@extends('layouts.guest')

@section('body_content')

<h1>Login with Your Tokenly Account</h1>

@include('partials.errors', ['errors' => $errors])


<p>You can login using your existing <a href="https://letstalkbitcoin.com">LetsTalkBitcoin.com</a> username and password or you can <a href="/auth/register">create a new Tokenly account</a>.</p>

<div class="spacer1"></div>

<form method="POST" action="/auth/login">

    {!! csrf_field() !!}

    <div class="form-group">
        <label for="Username">Username</label>
        <input required="required" name="username" type="text" class="form-control" id="Username" placeholder="satoshi" value="{{ old('username') }}">
    </div>

    <div class="form-group">
        <label for="Password">Password</label>
        <input required="required" name="password" type="password" class="form-control" id="Password" name="password">
    </div>


    <div class="checkbox">
      <label>
          <input type="checkbox" name="remember" id="RememberMe"> Remember Me
      </label>
    </div>


    <div>
        <button type="submit" class="btn btn-primary">Login</button>
    </div>

</form>


<div class="spacer4"></div>
<p><a href="/password/email">Forgot your password?</a></p>

<div class="spacer4"></div>
<p>Don't have an account yet?  <a href="/auth/register">Register</a></p>
<p>
	If you are using TOR and are having troubles logging in, make sure cookies are enabled (try temporarily disabling "always enable private browsing" in Privacy settings)
</p>

@endsection


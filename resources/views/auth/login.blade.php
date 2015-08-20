@extends('layouts.guest')

@section('body_content')

<h1>Login with Your Tokenly Account</h1>

@include('partials.errors', ['errors' => $errors])


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
<p>Don't have an account yet?  <a href="/auth/register">Register</a></p>

@endsection


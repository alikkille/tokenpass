@extends('layouts.guest')

@section('htmltitle', 'Password Reset')

@section('body_class', 'login')

@section('body_content')

<div class="everything">
  <div class="logo"><a href="/">token<strong>pass</strong></a></div>
    <h1 class="login-heading">Reset your password</h1>
    <div class="form-wrapper">
      @include('partials.errors', ['errors' => $errors])

      @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>

        <p>Please check your email and click the link sent to you.</p>
      @else
      <form method="POST" action="/password/reset">
        <input type="hidden" name="token" value="{{ $token }}">

        {!! csrf_field() !!}

        <input name="email" type="text" id="email" placeholder="me@myisp.com" value="{{ old('email') }}" required>

        <input type="password" id="password" name="password" placeholder="password" required>

        <input type="password" id="password_confirmation" placeholder="password confirmation" name="password_confirmation" required>


        <button type="submit" class="login-btn">Reset Password</button>
      </form>
    </div>
    <div class="login-subtext">
      <span>
        Don't have an account?
        <a href="/auth/register"><strong>Register</strong></a>
      </span>
    </div>
    <div class="or-divider-module">
      <div class="divider">.</div><span class="or">or</span>
      <div class="divider">.</div>
    </div><a class="signin-with-btc-btn" href="/auth/login">Cancel</a>
</div>

@endif

@endsection

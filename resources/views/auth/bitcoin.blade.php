@extends('layouts.guest')

@section('htmltitle', 'Login With Bitcoin')

@section('body_class', 'login')

@section('body_content')

<div class="everything">
  <div class="logo"><a href="/">token<strong>pass</strong></a></div>
    <div class="form-wrapper">
      @include('partials.errors', ['errors' => $errors])
      @if(Session::get('url.intended') !== NULL)
        <div><p class="alert-info">You are about to sign into <strong><a href={{\TKAccounts\Models\OAuthClient::getOAuthClientDetails(Session::get("url.intended"))["app_link"]}}>{{\TKAccounts\Models\OAuthClient::getOAuthClientDetails(Session::get('url.intended'))['name']}}!</a></strong></p>
        </div>
      @endif
      <form method="POST" action="/auth/bitcoin">
        {!! csrf_field() !!}

        <div class="tooltip-wrapper" data-tooltip="Login quickly and securely by signing todays Word of the Day with a previously verified address.">
          <i class="help-icon material-icons">help_outline</i>
        </div>
        <input name="btc-wotd" type="text" placeholder="btc-wotd" value="{{ $sigval }}" onclick="this.select();" readonly>

        <div class="tooltip-wrapper" data-tooltip="Paste your signed Word of the Day into this window, then click login.">
          <i class="help-icon material-icons">help_outline</i>
        </div>
        <textarea name="signed_message" placeholder="cryptographic signature" rows="5"></textarea>
        <button type="submit" class="login-btn">Login</button>
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
    </div><a class="signin-with-btc-btn" href="/auth/login">Sign In With Username</a>
</div>

@endsection

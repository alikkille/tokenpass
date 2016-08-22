@extends('layouts.guest')

@section('htmltitle', 'Login With Bitcoin')

@section('body_class', 'login')

@section('body_content')

    <div class="everything">
        <div class="logo"><a href="/">token<strong>pass</strong></a></div>
        <h1 class="login-heading">BTC Two Factor Authentication</h1>
        <div class="form-wrapper">
            @include('partials.alerts')
			@if(TKAccounts\Models\OAuthClient::getOAuthClientIDFromIntended())
				<div>
                    <p class="alert-info">
                        You are about to sign into 
                        <strong><a href="{{\TKAccounts\Models\OAuthClient::getOAuthClientDetailsFromIntended()["app_link"]}}" target="_blank">{{\TKAccounts\Models\OAuthClient::getOAuthClientDetailsFromIntended()['name']}}</a></strong>
                    </p>
				</div>
			@endif
            <form method="POST" action="/auth/signed">
                {!! csrf_field() !!}

                <div class="tooltip-wrapper" data-tooltip="Sign this message with a verified bitcoin address which has 2FA enabled, this is for your security">
                    <i class="help-icon material-icons">help_outline</i>
                </div>
                <input name="btc-wotd" type="text" placeholder="btc-wotd" value="{{ $sigval }}" onclick="this.select();" readonly>
                <input type="hidden" name="redirect" value="{{ $redirect }}">
                <div class="tooltip-wrapper" data-tooltip="Paste your signed message into this window, then click authenticate.">
                    <i class="help-icon material-icons">help_outline</i>
                </div>
                <textarea name="signed_message" placeholder="cryptographic signature" rows="5"></textarea>
                <button type="submit" class="login-btn">Authenticate</button>
                <p>
                    <strong><a href="web+pockets:sign?message={{ str_replace('+', '%20', urlencode($sigval)) }}&label={{ str_replace('+', '%20', urlencode('Tokenpass Two Factor Authentication')) }}&callback={{ urlencode(route('auth.sign')) }}">Sign with Pockets</a></strong>
                </p>                
            </form>
        </div>
    </div>

@endsection

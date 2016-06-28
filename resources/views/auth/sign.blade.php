@extends('accounts.base')

@section('htmltitle', 'Login With Bitcoin')

@section('body_class', 'dashboard login')

@section('accounts_content')

    <div class="everything">
        <div class="logo"><a href="/">token<strong>pass</strong></a></div>
        <div class="form-wrapper">
            @include('partials.errors', ['errors' => $errors])
            @if(Session::get('url.intended') !== NULL)
                <div><p class="alert-info">You are about to sign into <strong><a href={{\TKAccounts\Models\OAuthClient::getOAuthClientDetails(Session::get("url.intended"))["app_link"]}}>{{\TKAccounts\Models\OAuthClient::getOAuthClientDetails(Session::get('url.intended'))['name']}}!</a></strong></p>
                </div>
            @endif
            <form method="POST" action="/auth/signed">
                {!! csrf_field() !!}
                <p>
                    <strong>Bitcoin Two Factor Authentication</strong>
                </p>
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
            </form>
        </div>
    </div>

@endsection

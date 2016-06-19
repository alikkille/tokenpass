@extends('accounts.base')

@section('htmltitle', 'Login With Bitcoin')

@section('body_class', 'dashboard login')

@section('accounts_content')

    <div class="everything">
        <div class="logo"><a href="/">token<strong>pass</strong></a></div>
        <div class="form-wrapper">
            @include('partials.errors', ['errors' => $errors])
            <form method="POST" action="/auth/signed">
                {!! csrf_field() !!}

                <div class="tooltip-wrapper" data-tooltip="Sign this message, this is for your security">
                    <i class="help-icon material-icons">help_outline</i>
                </div>
                <input name="btc-wotd" type="text" placeholder="btc-wotd" value="{{ $sigval }}" onclick="this.select();" readonly>
                <input type="hidden" name="route" value="{{ $route }}">
                <div class="tooltip-wrapper" data-tooltip="Paste your signed Word of the Day into this window, then click authenticate.">
                    <i class="help-icon material-icons">help_outline</i>
                </div>
                <textarea name="signed_message" placeholder="cryptographic signature" rows="5"></textarea>
                <button type="submit" class="login-btn">Authenticate</button>
            </form>
        </div>
    </div>

@endsection

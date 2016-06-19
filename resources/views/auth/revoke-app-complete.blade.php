@extends('accounts.base')

@section('htmltitle', 'Revoke App Complete')

@section('body_class', 'dashboard revoke_app')

@section('accounts_content')

<section class="title">
  <span class="heading">Access Revoked Complete</span>
</section>

<section>
  <p>Access to client <strong>{{ $client['name'] }}</strong> was revoked.</p>
  <a class="revoke-btn" href="/auth/connectedapps">Return</a>
</section>

@endsection

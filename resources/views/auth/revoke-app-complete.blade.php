@extends('accounts.base')

@section('body_class') dashboard revoke_app @endsection

@section('accounts_content')

<section class="title">
  <span class="heading">Access Revoked Complete</span>
</section>

<section>
  <p>Access to client {{ $client['name'] }} was revoked.</p>
  <a href="/auth/connectedapps">Return</a>
</section>

@endsection

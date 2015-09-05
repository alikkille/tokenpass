@extends('accounts.base')

@section('accounts_content')


<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Access Revoked</h3>
      </div>
    <div class="panel-body">
        Access to client {{ $client['name'] }} was revoked.
    </div>
</div>

<div class="spacer1"></div>

<div>
    <a class="btn btn-default" href="/auth/connectedapps">Return</a>
</div>

@endsection

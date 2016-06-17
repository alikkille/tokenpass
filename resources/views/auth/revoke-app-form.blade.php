@extends('accounts.base')

@section('body_class') dashboard revoke_app @endsection

@section('accounts_content')

<section class="title">
  <span class="heading">Access Revoke</span>
</section>

<section>
  <p>Are you sure you want to revoke access for {{ $client['name'] }}?</p>
  <form method="POST" action="/auth/revokeapp/{{ $client['uuid'] }}">
    
    {!! csrf_field() !!}

    <div>
      <button type="submit" name="delete" value="1">Revoke Access</button>
      <a href="/auth/connectedapps">Return</a>
    </div>

  </form>
</section>

@endsection


@section('accounts_content')


<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">Revoke Access</h3>
      </div>
    <div class="panel-body">
        Are you sure you want to revoke access for {{ $client['name'] }}?
    </div>
</div>

<div class="spacer1"></div>

<form method="POST" action="/auth/revokeapp/{{ $client['uuid'] }}">

    {!! csrf_field() !!}

    <div class="spacer1"></div>

    <div>
        <a class="btn btn-default pull-right" href="/auth/connectedapps">Return</a>
        <button type="submit" name="delete" value="1" class="btn btn-danger">Revoke Access</button>
    </div>

</form>

@endsection

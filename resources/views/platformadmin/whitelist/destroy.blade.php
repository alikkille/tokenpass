@extends('platformAdmin::layouts.app')

@section('title_name') Whitelisted Address Deleted @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Whitelisted Address Deleted</h1>
    </div>

    <p>{{ goodbyeInterjection() }} This whitelisted address was deleted.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.whitelist.index') }}">Return to Promise Whitelist</a>
    </p>
</div>

@endsection


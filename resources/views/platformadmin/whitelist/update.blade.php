@extends('platformAdmin::layouts.app')

@section('title_name') Whitelisted Address Updated @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Whitelisted Address Updated</h1>
    </div>

    <p>{{ successInterjection() }} This whitelisted address was updated.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.whitelist.index') }}">Return to Promise Whitelist</a>
    </p>
</div>

@endsection


@extends('platformAdmin::layouts.app')

@section('title_name') Address Whitelisted @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Address Whitelisted</h1>
    </div>

    <p>{{ successInterjection() }} The whitelisted promise address was created.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.whitelist.index') }}">Return to Promise Whitelist</a>
    </p>
</div>

@endsection


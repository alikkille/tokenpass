@extends('platformAdmin::layouts.app')

@section('title_name') Connection Deleted @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Connection Deleted</h1>
    </div>

    <p>{{ goodbyeInterjection() }} This connection was deleted.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.connectedapps.index') }}">Return to Connections</a>
    </p>
</div>

@endsection


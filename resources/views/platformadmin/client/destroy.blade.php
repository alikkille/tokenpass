@extends('platformAdmin::layouts.app')

@section('title_name') Client Deleted @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Client Deleted</h1>
    </div>

    <p>{{ goodbyeInterjection() }} This client was deleted.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.client.index') }}">Return to Clients</a>
    </p>
</div>

@endsection


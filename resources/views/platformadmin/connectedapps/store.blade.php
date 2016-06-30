@extends('platformAdmin::layouts.app')

@section('title_name') Connection Created @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Connection Created</h1>
    </div>

    <p>{{ successInterjection() }} The connection was created.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.connectedapps.index') }}">Return to Connections</a>
    </p>
</div>

@endsection


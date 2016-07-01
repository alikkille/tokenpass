@extends('platformAdmin::layouts.app')

@section('title_name') Connection Updated @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Connection Updated</h1>
    </div>

    <p>{{ successInterjection() }} This connection was updated.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.connectedapps.index') }}">Return to Connections</a>
    </p>
</div>

@endsection


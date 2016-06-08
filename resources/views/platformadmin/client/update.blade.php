@extends('platformAdmin::layouts.app')

@section('title_name') Client Updated @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Client Updated</h1>
    </div>

    <p>{{ successInterjection() }} This client was updated.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.client.index') }}">Return to Clients</a>
    </p>
</div>

@endsection


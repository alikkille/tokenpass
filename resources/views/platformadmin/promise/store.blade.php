@extends('platformAdmin::layouts.app')

@section('title_name') Promise Created @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Promise Created</h1>
    </div>

    <p>{{ successInterjection() }} The promise was created.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.promise.index') }}">Return to Promises</a>
    </p>
</div>

@endsection


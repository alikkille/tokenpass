@extends('platformAdmin::layouts.app')

@section('title_name') Promise Deleted @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Promise Deleted</h1>
    </div>

    <p>{{ goodbyeInterjection() }} This promise was deleted.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.promise.index') }}">Return to Promises</a>
    </p>
</div>

@endsection


@extends('platformAdmin::layouts.app')

@section('title_name') Token Promise Updated @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Token Promise Updated</h1>
    </div>

    <p>{{ successInterjection() }} This promise was updated.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.promise.index') }}">Return to Promises</a>
    </p>
</div>

@endsection


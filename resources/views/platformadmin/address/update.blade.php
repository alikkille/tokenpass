@extends('platformAdmin::layouts.app')

@section('title_name') Pocket Address Updated @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Pocket Address Updated</h1>
    </div>

    <p>{{ successInterjection() }} This address was updated.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.address.index') }}">Return to Pocket Addresses</a>
    </p>
</div>

@endsection


@extends('platformAdmin::layouts.app')

@section('title_name') OAuth Scope Created @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>OAuth Scope Created</h1>
    </div>

    <p>{{ successInterjection() }} The scope was created.</p>

    <p style="margin-top: 6%;">
      <a class="button" href="{{ route('platform.admin.scopes.index') }}">Return to OAuth Scopes</a>
    </p>
</div>

@endsection


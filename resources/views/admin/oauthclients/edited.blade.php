@extends('layouts.admin')

@section('body')

<h1>OAuth Client Updated</h1>


<div class="spacer2"></div>

<p>Client Updated</p>

<a href="{{ route('admin.oauthclients.index' )}}" class="button">Return to OAuth Clients List</a>

@endsection

@extends('layouts.admin')

@section('body')

<h1>OAuth Client Created</h1>


<div class="spacer2"></div>

<p>Client Created</p>

<a href="{{ route('admin.oauthclients.index' )}}" class="button">Return to OAuth Clients List</a>

@endsection

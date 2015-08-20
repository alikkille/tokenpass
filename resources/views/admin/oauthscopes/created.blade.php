@extends('layouts.admin')

@section('body_content')

<h1>OAuth Scope Created</h1>


<div class="spacer2"></div>

<p>Scope Created</p>

<a href="{{ route('admin.oauthscopes.index' )}}" class="button">Return to OAuth Scopes List</a>

@endsection

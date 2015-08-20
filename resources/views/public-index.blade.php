@extends('layouts.base')

@section('body')

<h1>Welcome to Tokenly Accounts</h1>

<p>Tokenly Accounts is where you register and login for Tokenly services such as Swapbot.</p>

<div class="spacer2"></div>

<a href="/auth/login" class="btn btn-primary">Login</a>

<a href="/auth/register" class="btn btn-success">Register</a>

@endsection


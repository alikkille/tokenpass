@extends('layouts.base')

@section('body')

<h1>Register a New Tokenly Account</h1>

@include('partials.errors', ['errors' => $errors])

<div class="spacer2"></div>

<form method="POST" action="/auth/register">

    {!! csrf_field() !!}

    <div class="form-group">
        <label for="Name">Name</label>
        <input required="required" name="name" type="text" class="form-control" id="Name" placeholder="Satoshi Nakamoto" value="{{ old('name') }}">
    </div>

    <div class="form-group">
        <label for="Name">Username</label>
        <input required="required" name="username" type="text" class="form-control" id="Name" placeholder="satoshi" value="{{ old('username') }}">
    </div>

    <div class="form-group">
        <label for="Email">Email address</label>
        <input required="required" name="email" type="email" class="form-control" id="Email" placeholder="youremail@yourwebsite.com" value="{{ old('email') }}">
    </div>

    <div class="spacer1"></div>

    <div class="form-group">
        <label for="Password">Password</label>
        <input required="required" type="password" class="form-control" id="Password" name="password">
    </div>

    <div class="form-group">
        <label for="Password">Confirm Password</label>
        <input required="required" type="password" class="form-control" id="Password" name="password_confirmation">
    </div>

    <div class="spacer2"></div>

    <div>
        <button type="submit" class="btn btn-primary">Register</button>
    </div>

</form>

@endsection

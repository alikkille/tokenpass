@extends('accounts.base')

@section('body_class') dashboard inventory @endsection

@section('accounts_content')

<h1>Your Dashboard</h1>

@if (!$user->emailIsConfirmed())
    <div class="alert alert-danger" role="alert">
        <p><strong>Note</strong>: You must confirm your email address by clicking the link in the email you received before you can use your account.</p>
        <p><a href="/auth/sendemail" class="alert-link">Send the email confirmation again</a>.</p>
    </div>
@endif

<h3>Welcome, {{$user['name']}}</h3>

<p>You are logged in as user {{ $user['username']}}. </p>

<div class="spacer2"></div>

<a href="/auth/update" class="btn btn-primary">Update your Profile</a>
<a href="/auth/logout" class="btn btn-danger">Logout</a>

@endsection
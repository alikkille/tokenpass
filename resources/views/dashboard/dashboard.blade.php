@extends('layouts.one-column')

@section('htmlTitle', 'Dashboard')

@section('pageTitle', 'Welcome')

@section('bodyContent')
    <div class="row">
        <div class="columns">
            <p>Welcome, {{ $currentUser->username }}.</p>
            <p>This is your dashboard, where you will be able to change your account settings.</p>
        </div>
    </div>

    <div class="spacer4"></div>

    <div class="row">
        <div class="columns">
            <a class="small button" href="/auth/logout">Logout</a>
        </div>
    </div>
@stop

@extends('layouts.guest')

@section('body_content')

@if ($errors and count($errors) > 0)
    <h1>Email Confirmation Failed</h1>
    @include('partials.alerts')
@else

    <h1>Tokenly Account Email Confirmed</h1>

    <div class="spacer2"></div>

    <p>Thank you for confirming your email address.  Your email address is now confirmed.</p>

    <div class="spacer2"></div>

    <p><a class="btn btn-default" href="/dashboard">Return to Dashboard or Login</a></p>

@endif

@endsection


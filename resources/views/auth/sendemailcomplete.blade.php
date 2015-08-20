@extends('accounts.base')

@section('accounts_content')

<h1>Confirm My Tokenly Account Email</h1>

<div class="spacer2"></div>

<p>A confirmation email was sent to {{ $model['email'] }}. Please click the link in that email to confirm your email address.</p>

<div class="spacer4"></div>
<p><a class="btn btn-default" href="/dashboard">Return to Dashboard</a></p>




@endsection

@extends('accounts.base')

@section('accounts_content')

<h1>Confirm My Tokenly Account Email</h1>

@include('partials.errors', ['errors' => $errors])

<div class="spacer2"></div>

<form method="POST" action="/auth/sendemail">

    {!! csrf_field() !!}

    <p>To send a confirmation email to {{ $model['email'] }}, please click the button below.</p>

    <div class="spacer3"></div>

    <div>
        <button type="submit" class="btn btn-primary">Send Confirmation Email</button>
    </div>

</form>

<div class="spacer4"></div>
<p><a class="btn btn-default" href="/dashboard">Cancel</a></p>




@endsection

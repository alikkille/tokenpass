@extends('layouts.guest')

@section('body_content')

<h1>Reset Your Tokenpass Password</h1>

@include('partials.errors', ['errors' => $errors])

@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>

    <p>Please check your email and click the link sent to you.</p>
@else

<form method="POST" action="/password/email">

    {!! csrf_field() !!}

    <div class="form-group">
        <label for="Email">Email</label>
        <input required="required" name="email" type="text" class="form-control" id="Email" placeholder="me@myisp.com" value="{{ old('email') }}">
    </div>

    <div>
        <button type="submit" class="btn btn-success">Send Password Reset Link</button>
    </div>

</form>


<div class="spacer4"></div>

<p><a class="btn btn-default" href="/auth.login">Cancel</a></p>

@endif



@endsection

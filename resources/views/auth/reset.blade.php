@extends('layouts.guest')

@section('body_content')

<h1>Reset Your Tokenpass Password</h1>

@include('partials.errors', ['errors' => $errors])


<form method="POST" action="/password/reset">

    {!! csrf_field() !!}
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
        <label for="Email">Email</label>
        <input required="required" name="email" type="text" class="form-control" id="Email" placeholder="me@myisp.com" value="{{ old('email') }}">
    </div>

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
        <button type="submit" class="btn btn-success">Reset Password</button>
    </div>

</form>


@endsection


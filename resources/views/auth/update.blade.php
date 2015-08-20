@extends('accounts.base')

@section('accounts_content')

<h1>Edit My Tokenly Account</h1>

@include('partials.errors', ['errors' => $errors])

<div class="spacer2"></div>

<form method="POST" action="/auth/update">

    {!! csrf_field() !!}

    <div class="form-group">
        <label for="Name">Name</label>
        <input required="required" name="name" type="text" class="form-control" id="Name" placeholder="Satoshi Nakamoto" value="{{ old('name') }}">
    </div>

    <div class="form-group">
        <label for="Name">Username</label>
        <p class="form-control-static">{{ $model['username'] }}</p>
    </div>

    <div class="form-group">
        <label for="Email">Email address</label>
        <input required="required" name="email" type="email" class="form-control" id="Email" placeholder="youremail@yourwebsite.com" value="{{ old('email') }}">
    </div>

    <div class="spacer2"></div>

    <p>Enter a new password only if you wish to update your password</p>

    <div class="form-group">
        <label for="Password">New Password</label>
        <input type="password" class="form-control" id="Password" name="new_password">
    </div>

    <div class="form-group">
        <label for="Password">Confirm New Password</label>
        <input type="password" class="form-control" id="Password" name="new_password_confirmation">
    </div>

    <div class="spacer2"></div>


    <p>Verify your current password to save your changes</p>

    <div class="form-group">
        <label for="Password">Current Password</label>
        <input required="required" type="password" class="form-control" id="Password" name="password">
    </div>


    <div class="spacer3"></div>

    <div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>

</form>

<div class="spacer4"></div>
<p><a class="btn btn-default" href="/dashboard">Cancel</a></p>




@endsection

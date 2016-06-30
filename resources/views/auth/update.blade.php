@extends('accounts.base')

@section('htmltitle', 'Account Settings')

@section('body_class', 'dashboard')

@section('accounts_content')

<section class="title">
    <span class="heading">Account Settings</span>
</section>

<section>
    @include('partials.errors', ['errors' => $errors])

    @if(Session::has('message'))
        <p class="alert {{ Session::get('message-class') }}">{{ Session::get('message') }}</p>
    @endif	

    <form method="POST" action="/auth/update">

        {!! csrf_field() !!}

        <label for="Name">Name</label>
        <input name="name" type="text" id="Name" placeholder="Satoshi Nakamoto" value="{{ old('name') }}">

        <label for="Name">Username</label>
        <input value="{{ $model['username'] }}" readonly>

        <label for="Email">Email address</label>
        <input required="required" name="email" type="email" id="Email" placeholder="youremail@yourwebsite.com" value="{{ old('email') }}">

        <div class="input-group">
            <label for="Password">New Password</label>
            <input type="password" id="Password" name="new_password">
            <div class="sublabel">Enter a new password only if you wish to update your password</div>
        </div>

        <label for="Password">Confirm New Password</label>
        <input type="password" id="Password" name="new_password_confirmation">

        <div class="input-group">
            <label>Enable Second Factor on account?</label>
            <input id="account-second-factor" name="second_factor" type="checkbox" class="toggle toggle-round-flat" @if($model->second_factor == 1) checked="checked" @endif value="1" >
            <label for="account-second-factor"></label>
        </div>

        <hr>
        
        <div class="input-group">
            <label for="Password">Current Password</label>
            <input required="required" type="password" id="Password" name="password">
            <div class="sublabel">Please verify your current password to save your changes</div>
        </div>
        
        <button type="submit">Save</button>

    </form>
</section>

@endsection

@extends('accounts.base')

@section('body_class') dashboard @endsection

@section('accounts_content')

<section class="title">
    <span class="heading">Account Settings</span>
</section>

<section class="panels-container">
    <div class="panels">
        <div class="panel">
            <div class="panel-heading">
                Update Account
            </div>
            <div class="panel-content">
                @include('partials.errors', ['errors' => $errors])

                <form method="POST" action="/auth/update">

                    {!! csrf_field() !!}

                    <label for="Name">Name</label>
                    <input required="required" name="name" type="text" id="Name" placeholder="Satoshi Nakamoto" value="{{ old('name') }}">

                    <label for="Name">Username</label>
                    <p class="form-control-static">{{ $model['username'] }}</p>

                    <label for="Email">Email address</label>
                    <input required="required" name="email" type="email" id="Email" placeholder="youremail@yourwebsite.com" value="{{ old('email') }}">

                    <div class="sublabel">Enter a new password only if you wish to update your password</div>
                    <label for="Password">New Password</label>
                    <input type="password" id="Password" name="new_password">

                    <label for="Password">Confirm New Password</label>
                    <input type="password" id="Password" name="new_password_confirmation">

                    <div class="sublabel">Please verify your current password to save your changes</div>
                    <label for="Password">Current Password</label>
                    <input required="required" type="password" id="Password" name="password">

                    <button type="submit">Save</button>

                </form>
            </div>
        </div>
    </div>
</section>

@endsection

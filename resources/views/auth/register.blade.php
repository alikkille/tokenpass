@extends('layouts.guest')

@section('body_class') login @endsection

@section('body_content')
<div class="everything">
    <div class="logo"><a href="/">token<strong>pass</strong></a></div>
        <div class="form-wrapper">
            @include('partials.errors', ['errors' => $errors])
            <form method="POST" action="/auth/register">
                {!! csrf_field() !!}
                <input type="text" name="name" placeholder="name" value="{{ old('name') }}" required>
                <input type="text" name="username" placeholder="username" value="{{ old('username') }}" required>
                <input type="email" name="email" placeholder="email" value="{{ old('email') }}" required>
                <input type="password" name="password" placeholder="password" required>
                <input type="password" name="password_confirmation" placeholder="password (again)" required>
                <button type="submit" class="login-btn">Register</button>
            </form>
        </div>
        <div class="register-subtext">
            <span>Already have an account? <a href="/auth/login">Login</a></span>
        </div>
</div>
@endsection


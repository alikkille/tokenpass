@extends('layouts.base')

@section('body')

<h1>Register a New Tokenly Account</h1>

@include('partials.errors', ['errors' => $errors])


<form method="POST" action="/auth/login">

    {!! csrf_field() !!}

    <div class="form-group">
        <label for="Email">Email Address</label>
        <input required="required" name="email" type="email" class="form-control" id="Email" placeholder="youremail@yourwebsite.com" value="{{ old('email') }}">
    </div>

    <div class="form-group">
        <label for="Password">Password</label>
        <input required="required" name="password" type="password" class="form-control" id="Password" name="password">
    </div>


    <div class="checkbox">
      <label>
          <input type="checkbox" name="remember" id="RememberMe"> Remember Me
      </label>
    </div>


    <div>
        <button type="submit" class="btn btn-primary">Login</button>
    </div>

</form>

@endsection


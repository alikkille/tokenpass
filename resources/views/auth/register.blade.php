@extends('layouts.one-column')

@section('htmlTitle', 'Register')

@section('pageTitle', 'Register a Tokenly Account')

@section('bodyContent')

            @include('partials.errors')

            {!! Form::open(['url' => '/auth/register', 'method' => 'post']) !!}
            
            {!! Form::label('email', 'E-Mail Address', []) !!}
            {!! Form::email('email') !!}

            {!! Form::label('username', 'Username', []) !!}
            {!! Form::text('username') !!}

            {!! Form::label('password', 'Password', []) !!}
            {!! Form::password('password') !!}

            {!! Form::label('password_confirmation', 'Confirm Password', []) !!}
            {!! Form::password('password_confirmation') !!}

            {!! Form::submit('Register', ['class' => 'success button']) !!}
            <a href="/" class="secondary button right">Cancel</a>

            {!! Form::close() !!}


@stop

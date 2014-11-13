@extends('layouts.one-column')

@section('htmlTitle', 'Login')

@section('pageTitle', 'Login Using your Tokenly Account')


@section('bodyContent')

            @include('partials.errors')

            {!! Form::open(['url' => '/auth/login', 'method' => 'post']) !!}
            
            {!! Form::label('email', 'E-Mail Address', []) !!}
            {!! Form::email('email') !!}

            {!! Form::label('password', 'Password', []) !!}
            {!! Form::password('password') !!}


            {!! Form::submit('Login', ['class' => 'success button']) !!}
            <a href="/" class="secondary button right">Cancel</a>

            {!! Form::close() !!}


@stop

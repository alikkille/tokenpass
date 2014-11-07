@extends('layouts.one-column')

@section('htmlTitle', 'New Password')

@section('pageTitle', 'Choose a New Password')

@section('bodyContent')

            @include('partials.errors')

            {!! Form::open(['route' => 'password.reset']) !!}

            {!! Form::label('password', 'Password', []) !!}
            {!! Form::password('password') !!}

            {!! Form::label('password_confirmation', 'Confirm Password', []) !!}
            {!! Form::password('password_confirmation') !!}

            {!! Form::hidden('token', $token) !!}

            {!! Form::submit('Set New Password', ['class' => 'button']) !!}

            {!! Form::close() !!}


@stop

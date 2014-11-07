@extends('layouts.one-column')

@section('htmlTitle', 'Reset Password')

@section('pageTitle', 'Reset My Password')

@section('bodyContent')

            @include('partials.errors')

            {!! Form::open(['route' => 'password.email']) !!}
            
            {!! Form::label('email', 'E-Mail Address', []) !!}
            {!! Form::email('email') !!}


            {!! Form::submit('Send Reset Email', ['class' => 'button']) !!}

            {!! Form::close() !!}


@stop

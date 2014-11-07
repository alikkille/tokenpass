@extends('layouts.one-column')

@section('htmlTitle', 'Grant Access')

@section('pageTitle', 'Authorize This Application')


@section('bodyContent')

            @include('partials.errors')

            {!! Form::open(['route' => 'oauth.authorize', 'method' => 'post', 'url' => Request::fullUrl()]) !!}

            {!! Form::submit('Grant Access', ['name' => 'approve', 'class' => 'button']) !!}
            {!! Form::submit('Deny Access', ['name' => 'deny', 'class' => 'secondary button']) !!}

            {!! Form::close() !!}


@stop

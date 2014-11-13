@extends('layouts.one-column')

@section('htmlTitle', 'Grant Access')

@section('pageTitle', 'Authorize This Application')


@section('bodyContent')

            <p>The application <span class="clientName">{{{ $client->getName() }}}</span> has requested the following privileges:</p>
            <ul>
            @foreach ($scopes as $scope)
            <li>{{{ $scope->getDescription() }}} </li>
            @endforeach
            </ul>

            <p>What would you like to do?</p>

            @include('partials.errors')

            {!! Form::open(['route' => 'oauth.authorize', 'method' => 'post', 'url' => Request::fullUrl()]) !!}

            {!! Form::submit('Grant Access', ['name' => 'approve', 'class' => 'success button']) !!}
            {!! Form::submit('Deny Access', ['name' => 'deny', 'class' => 'secondary button']) !!}

            {!! Form::close() !!}


@stop

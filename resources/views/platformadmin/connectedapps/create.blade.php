@extends('platformAdmin::layouts.app')

@section('title_name') Create Connection @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Create Connection</h1>
    </div>

    @include('platformAdmin::includes.errors')


    {!! Form::open([
        'method' => 'POST',
        'route' => ['platform.admin.connectedapps.store'],
    ]) !!}

    <div class="row">
        <div class="six columns">
            {!! Form::label('client_id', 'Client') !!}
            {!! Form::select('client_id', $client_options, null, ['class' => 'u-full-width']) !!}
        </div>

        <div class="six columns">
            {!! Form::label('user_id', 'User') !!}
            {!! Form::select('user_id', $user_options, null, ['class' => 'u-full-width']) !!}
        </div>

        <div class="six columns">
        </div>
    </div>


    <div class="row" style="margin-top: 3%;">
        <div class="three columns">
            {!! Form::submit('Create', ['class' => 'button-primary u-full-width']) !!}
        </div>
        <div class="six columns">&nbsp;</div>
        <div class="three columns">
            <a class="button u-full-width" href="{{ route('platform.admin.connectedapps.index') }}">Cancel</a>
        </div>
    </div>

    {!! Form::close() !!}

</div>

@endsection


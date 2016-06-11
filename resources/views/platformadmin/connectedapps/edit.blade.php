@extends('platformAdmin::layouts.app')

@section('title_name') Edit Connection @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Edit Connection {{ $model['id'] }}</h1>
    </div>

    @include('platformAdmin::includes.errors')


    {!! Form::model($model, [
        'method' => 'PATCH',
        'route' => ['platform.admin.connectedapps.update', $model['id']],
    ]) !!}

    <div class="row">
        <div class="six columns">
            {!! Form::label('client_id', 'Client') !!}
            {!! Form::select('client_id', $client_options, $model['client_id'], ['class' => 'u-full-width']) !!}
        </div>

        <div class="six columns">
            {!! Form::label('user_id', 'User') !!}
            {!! Form::select('user_id', $user_options, $model['user_id'], ['class' => 'u-full-width']) !!}
        </div>

        <div class="six columns">
        </div>
    </div>




    <div class="row" style="margin-top: 3%;">
        <div class="three columns">
            {!! Form::submit('Update', ['class' => 'button-primary u-full-width']) !!}
        </div>
        <div class="six columns">&nbsp;</div>
        <div class="three columns">
            <a class="button u-full-width" href="{{ route('platform.admin.connectedapps.index') }}">Cancel</a>
        </div>
    </div>

    {!! Form::close() !!}

</div>

@endsection


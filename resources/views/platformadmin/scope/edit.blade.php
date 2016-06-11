@extends('platformAdmin::layouts.app')

@section('title_name') Edit OAuth Scope @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Edit OAuth Scope {{ $model['name'] }}</h1>
    </div>

    @include('platformAdmin::includes.errors')


    {!! Form::model($model, [
        'method' => 'PATCH',
        'route' => ['platform.admin.scopes.update', $model['id']],
    ]) !!}

    <div class="row">
        <div class="six columns">
            {!! Form::label('id', 'Scope ID') !!}
            {!! Form::text('id', $model['id'], ['class' => 'u-full-width']) !!}
            <br><br>
            {!! Form::label('description', 'Description') !!}
            {!! Form::textarea('description', $model['description'], ['class' => 'u-full-width', ]) !!}               
                  
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
            <a class="button u-full-width" href="{{ route('platform.admin.scopes.index') }}">Cancel</a>
        </div>
    </div>

    {!! Form::close() !!}

</div>

@endsection


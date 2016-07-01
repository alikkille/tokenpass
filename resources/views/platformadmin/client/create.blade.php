@extends('platformAdmin::layouts.app')

@section('title_name') Create Client @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Create Client</h1>
    </div>

    @include('platformAdmin::includes.errors')


    {!! Form::open([
        'method' => 'POST',
        'route' => ['platform.admin.client.store'],
    ]) !!}

    <div class="row">

        <div class="six columns">
            {!! Form::label('name', 'Client Name') !!}
            {!! Form::text('name', null, ['class' => 'u-full-width', 'placeholder' => 'Jethro\'s Token Emporium', ]) !!}
        </div>

        <div class="six columns">
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
            <a class="button u-full-width" href="{{ route('platform.admin.client.index') }}">Cancel</a>
        </div>
    </div>

    {!! Form::close() !!}

</div>

@endsection


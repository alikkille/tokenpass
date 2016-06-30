@extends('platformAdmin::layouts.app')

@section('title_name') Whitelist Token Promise Address @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Whitelist Token Promise Address</h1>
    </div>

    @include('platformAdmin::includes.errors')


    {!! Form::open([
        'method' => 'POST',
        'route' => ['platform.admin.whitelist.store'],
    ]) !!}
    <div class="row">
        <div class="six columns">
            {!! Form::label('address', 'Address') !!}
            {!! Form::text('address', null, ['class' => 'u-full-width']) !!}
        </div>
    </div>
    <div class="row">
        <div class="six columns">
            {!! Form::label('proof', 'Proof Signature') !!}
            {!! Form::textarea('proof', null, ['class' => 'u-full-width']) !!}
        </div>

        <div class="six columns">
            {!! Form::label('assets', 'Allowed Assets') !!}
            {!! Form::textarea('assets', null, ['class' => 'u-full-width']) !!}
        </div>
    </div>
    <div class="row">
        <div class="six columns">
            {!! Form::label('client_id', 'Client App ID') !!}
            <select id="client_id" name="client_id">
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" >{{ $client->name }} - {{ $client->id }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row" style="margin-top: 3%;">
        <div class="three columns">
            {!! Form::submit('Create', ['class' => 'button-primary u-full-width']) !!}
        </div>
        <div class="six columns">&nbsp;</div>
        <div class="three columns">
            <a class="button u-full-width" href="{{ route('platform.admin.whitelist.index') }}">Cancel</a>
        </div>
    </div>

    {!! Form::close() !!}

</div>

@endsection


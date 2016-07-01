@extends('platformAdmin::layouts.app')

@section('title_name') Create Promise @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Create Promise</h1>
    </div>

    @include('platformAdmin::includes.errors')


    {!! Form::open([
        'method' => 'POST',
        'route' => ['platform.admin.promise.store'],
    ]) !!}

    <div class="row">
        <div class="six columns">
            {!! Form::label('source', 'Source Address') !!}
            {!! Form::text('source', $model['source'], ['class' => 'u-full-width']) !!}
        </div>

        <div class="six columns">
            {!! Form::label('destination', 'Destination Address') !!}
            {!! Form::text('destination', $model['destination'], ['class' => 'u-full-width']) !!}
        </div>
    </div>

    <div class="row">
        <div class="four columns">
            {!! Form::label('quantity', 'Quantity') !!}
            {!! Form::text('quantity', $model['quantity'], ['class' => 'u-full-width']) !!}
        </div>

        <div class="four columns">
            {!! Form::label('asset', 'Asset') !!}
            {!! Form::text('asset', $model['asset'], ['class' => 'u-full-width']) !!}
        </div>
        <div class="four columns">
        </div>
    </div>
    <div class="row">
        <div class="four columns">
            {!! Form::label('expiration', 'Expiration Date') !!}
            {!! Form::text('expiration', null, ['class' => 'u-full-width']) !!}
        </div>

        <div class="four columns">
            {!! Form::label('ref', 'Reference data') !!}
            {!! Form::text('ref', null, ['class' => 'u-full-width']) !!}
        </div>
    </div>
    <div class="row">
        <div class="four columns">
            {!! Form::label('txid', 'TX ID') !!}
            {!! Form::text('txid', null, ['class' => 'u-full-width']) !!}
        </div>

        <div class="four columns">
            {!! Form::label('fingerprint', 'TX Fingerprint') !!}
            {!! Form::text('fingerprint', null, ['class' => 'u-full-width']) !!}
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
            <a class="button u-full-width" href="{{ route('platform.admin.promise.index') }}">Cancel</a>
        </div>
    </div>

    {!! Form::close() !!}

</div>

@endsection


@extends('platformAdmin::layouts.app')

@section('title_name') Edit Promise @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Edit Promise {{ $model['id'] }}</h1>
    </div>

    @include('platformAdmin::includes.errors')


    {!! Form::model($model, [
        'method' => 'PATCH',
        'route' => ['platform.admin.promise.update', $model['id']],
    ]) !!}
    <p>
        <strong>Created At:</strong> {{ $model->created_at->format('F j\, Y \a\t g:i A') }}<br>
        <strong>Updated At:</strong> {{ $model->updated_at->format('F j\, Y \a\t g:i A') }}
    </p>
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
            {!! Form::text('quantity', Tokenly\CurrencyLib\CurrencyUtil::satoshisToValue($model['quantity']), ['class' => 'u-full-width']) !!}
        </div>

        <div class="four columns">
            {!! Form::label('asset', 'Asset') !!}
            {!! Form::text('asset', $model['asset'], ['class' => 'u-full-width']) !!}
        </div>
    </div>

    <div class="row">
        <div class="four columns">
            {!! Form::label('expiration', 'Expiration Date') !!}
            {!! Form::text('expiration', date('Y-m-d H:i:s', intval($model['expiration'])), ['class' => 'u-full-width']) !!}
        </div>

        <div class="four columns">
            {!! Form::label('ref', 'Reference data') !!}
            {!! Form::text('ref', $model['ref'], ['class' => 'u-full-width']) !!}
        </div>
    </div>
    <div class="row">
        <div class="four columns">
            {!! Form::label('txid', 'TX ID') !!}
            {!! Form::text('txid', $model['txid'], ['class' => 'u-full-width']) !!}
        </div>

        <div class="four columns">
            {!! Form::label('fingerprint', 'TX Fingerprint') !!}
            {!! Form::text('fingerprint', $model['fingerprint'], ['class' => 'u-full-width']) !!}
        </div>
    </div>
    <div class="row">
        <div class="six columns">
            {!! Form::label('client_id', 'Client App ID') !!}
            <select id="client_id" name="client_id">
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" @if($client->id == $model['client_id']) selected @endif >{{ $client->name }} - {{ $client->id }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row" style="margin-top: 3%;">
        <div class="three columns">
            {!! Form::submit('Update', ['class' => 'button-primary u-full-width']) !!}
        </div>
        <div class="six columns">&nbsp;</div>
        <div class="three columns">
            <a class="button u-full-width" href="{{ route('platform.admin.promise.index') }}">Cancel</a>
        </div>
    </div>

    {!! Form::close() !!}

</div>

@endsection


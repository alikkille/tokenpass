@extends('platformAdmin::layouts.app')

@section('title_name') Edit Whitelisted Address @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Edit Whitelisted Address</h1>
    </div>

    @include('platformAdmin::includes.errors')


    {!! Form::model($model, [
        'method' => 'PATCH',
        'route' => ['platform.admin.whitelist.update', $model['id']],
    ]) !!}
    <p>
        <strong>Created At:</strong> {{ $model->created_at->format('F j\, Y \a\t g:i A') }}<br>
        <strong>Updated At:</strong> {{ $model->updated_at->format('F j\, Y \a\t g:i A') }}
    </p>
    <div class="row">
        <div class="six columns">
            {!! Form::label('address', 'Address') !!}
            {!! Form::text('address', $model['address'], ['class' => 'u-full-width']) !!}
        </div>
    </div>    
    <div class="row">
        <div class="six columns">
            {!! Form::label('proof', 'Proof Signature') !!}
            {!! Form::textarea('proof', $model['proof'], ['class' => 'u-full-width']) !!}
        </div>

        <div class="six columns">
            {!! Form::label('assets', 'Allowed Assets') !!}
            {!! Form::textarea('assets', $model['assets'], ['class' => 'u-full-width']) !!}
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
            <a class="button u-full-width" href="{{ route('platform.admin.whitelist.index') }}">Cancel</a>
        </div>
    </div>

    {!! Form::close() !!}

</div>

@endsection


@extends('layouts.admin')

@section('body')

<h1>Create an OAuth Client</h1>

@include('partials.errors', ['errors' => $errors])

<div class="spacer2"></div>

{!! Form::model($model, [
    'method' => 'PATCH',
    'route' => ['admin.oauthclients.update', $model['id']],
]) !!}

<div class="form-group">
    {!! Form::label('name', 'Client Name', ['class' => 'control-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('id', 'Client ID', ['class' => 'control-label']) !!}
    <p class="form-control-static">{{$model['id']}}</p>
</div>

<div class="form-group">
    {!! Form::label('secret', 'Client Secret', ['class' => 'control-label']) !!}
    <p class="form-control-static">{{$model['secret']}}</p>
</div>

<div class="form-group">
    {!! Form::label('endpoints', 'Client Endpoints', ['class' => 'control-label']) !!} <small class="pull-right">One per line</small>
    {!! Form::textarea('endpoints', null, ['class' => 'form-control']) !!}
</div>



{!! Form::submit('Update OAuth Client', ['class' => 'btn btn-primary']) !!}

{!! Form::close() !!}

<div class="spacer4"></div>
<a href="{{ route('admin.oauthclients.index') }}" class="btn btn-default">Cancel</a>


@endsection

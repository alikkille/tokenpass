@extends('layouts.admin')

@section('body_content')

<h1>Create an OAuth Client</h1>

@include('partials.errors', ['errors' => $errors])

<div class="spacer2"></div>

{!! Form::open([
    'route' => 'admin.oauthclients.store'
]) !!}

<div class="form-group">
    {!! Form::label('name', 'Client Name', ['class' => 'control-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

{{-- <div class="form-group">
    {!! Form::label('id', 'Client ID', ['class' => 'control-label']) !!}
    {!! Form::text('id', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('secret', 'Client Secret', ['class' => 'control-label']) !!}
    {!! Form::text('secret', null, ['class' => 'form-control']) !!}
</div>
 --}}


<div class="form-group">
    {!! Form::label('endpoints', 'Client Endpoints', ['class' => 'control-label']) !!} <small class="pull-right">One per line</small>
    {!! Form::textarea('endpoints', null, ['class' => 'form-control']) !!}
</div>



{!! Form::submit('Create New OAuth Client', ['class' => 'btn btn-primary']) !!}

{!! Form::close() !!}


<div class="spacer4"></div>
<a href="{{ route('admin.oauthclients.index') }}" class="btn btn-default">Cancel</a>


@endsection

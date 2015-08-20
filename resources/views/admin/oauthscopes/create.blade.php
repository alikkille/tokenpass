@extends('layouts.admin')

@section('body_content')

<h1>Create an OAuth Scope</h1>

@include('partials.errors', ['errors' => $errors])

<div class="spacer2"></div>

{!! Form::open([
    'route' => 'admin.oauthscopes.store'
]) !!}


<div class="form-group">
    {!! Form::label('id', 'Scope ID', ['class' => 'control-label']) !!}
    {!! Form::text('id', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Scope Description', ['class' => 'control-label']) !!}
    {!! Form::textarea('description', null, ['class' => 'form-control']) !!}
</div>


{!! Form::submit('Create New OAuth Scope', ['class' => 'btn btn-primary']) !!}

{!! Form::close() !!}


<div class="spacer4"></div>
<a href="{{ route('admin.oauthscopes.index') }}" class="btn btn-default">Cancel</a>


@endsection

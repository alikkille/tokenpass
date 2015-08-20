@extends('layouts.admin')

@section('body')

<h1>OAuth Scopes</h1>

{{-- @include('partials.errors', ['errors' => $errors]) --}}

<div class="spacer2"></div>

<table class="table table-striped table-condensed">
    <tr>
        <th>ID</th>
        <th>Description</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>
@foreach ($models as $model)
    <tr>
        <td>{{$model['id']}}</td>
        <td>{{$model['description']}}</td>
        <td><a href="{{ route('admin.oauthscopes.edit', $model['id']) }}" class="btn btn-primary">Edit</a></td>
        <td>
            {!! Form::open(['onSubmit' => "return confirm('Are you sure you want to delete this?')", 'method' => 'DELETE', 'route' => ['admin.oauthscopes.destroy', $model['id']]]) !!}
                {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
            {!! Form::close() !!}
        </td>
    </tr>
@endforeach


</table>

<a href="{{ route('admin.oauthscopes.create') }}" class="btn btn-primary">Create a New Scope</a>


@endsection

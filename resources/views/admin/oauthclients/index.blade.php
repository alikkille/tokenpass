@extends('layouts.admin')

@section('body_content')

<h1>OAuth Clients</h1>

{{-- @include('partials.errors', ['errors' => $errors]) --}}

<div class="spacer2"></div>

<table class="table table-striped table-condensed">
    <tr>
        <th>Name</th>
        <th>ID</th>
        <th>Owner</th>
        <th># Users</th>
        <th></th>
        <th></th>
    </tr>
@foreach ($models as $model)
    <tr>
        <td><strong>{{$model['name']}}</strong></td>
        <td>{{$model['id']}}</td>
        <td>
			@if($model->user_id == 0)
				Platform
			@elseif(!$model->owner)
				N/A
			@else
				{{ $model->owner->email }}
			@endif
		</td>
        <td>{{ number_format($model->user_count) }}</td>
        <td>
			<a href="{{ route('admin.oauthclients.edit', $model['id']) }}" class="btn btn-primary">Edit</a>
		</td>
        <td>	
            {!! Form::open(['onSubmit' => "return confirm('Are you sure you want to delete this?')", 'method' => 'DELETE', 'route' => ['admin.oauthclients.destroy', $model['id']]]) !!}
                {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
            {!! Form::close() !!}
        </td>
    </tr>
@endforeach


</table>

<a href="{{ route('admin.oauthclients.create') }}" class="btn btn-primary">Create a New Client</a>


@endsection

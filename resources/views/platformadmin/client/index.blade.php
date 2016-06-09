@extends('platformAdmin::layouts.app')

@section('title_name') Clients @endsection

@section('body_content')

<div class="container" style="margin-top: 3%">
    <div class="row">
        <h1>Clients</h1>
    </div>

    <table class="u-full-width">
      <thead>
        <tr>
          <th>Name</th>
          <th>Owner</th>
          <th>ID</th>
          <th>Connections</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($models as $model)
        <tr>
          <td>{{ $model['name'] }}</td>
          <td>
              <strong>
            @if($model['user_id'] == 0)
                Platform
            @else
                {{ $model->user()->username }}
            @endif
                </strong>
          </td>
          <td>{{ $model['id'] }}</td>
          <td>{{ $model->countConnections() }}</td>
          <td>
            <a class="button button-primary" href="{{ route('platform.admin.client.edit', ['id' => $model['id']]) }}">Edit</a>

            {{-- inline delete form --}}
            <form onsubmit="return confirm('Are you sure you want to delete this balance?')" action="{{ route('platform.admin.client.destroy', ['id' => $model['id']]) }}" method="POST" style="margin-bottom: 0; display: inline;">
            <input type="hidden" name="_method" value="DELETE">
              <button type="submit" class="button-primary">Delete</button>
            </form>

          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="row">
      <a href="{{ route('platform.admin.client.create') }}" class="button button-primary">Create a New Client</a>
    </div>
</div>


@endsection


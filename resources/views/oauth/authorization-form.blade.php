@extends('layouts.base')

@section('body')

<h1>Authorize this Application</h1>

@include('partials.errors', ['errors' => $errors])


<p>The application <span class="clientName">{{{ $client->getName() }}}</span> has requested the following privileges:</p>
<ul class="list-unstyled">
@foreach ($scopes as $scope)
<li>{{{ $scope->getDescription() }}} </li>
@endforeach
</ul>

<p>What would you like to do?</p>


<form method="POST" action="/oauth/authorize">

    {!! csrf_field() !!}

    <input type="hidden" name="client_id" value="{{$params['client_id']}}" />
    <input type="hidden" name="redirect_uri" value="{{$params['redirect_uri']}}" />
    <input type="hidden" name="response_type" value="{{$params['response_type']}}" />
    <input type="hidden" name="state" value="{{$params['state']}}" />

    <div class="spacer1"></div>

    <div>
        <button type="submit" class="btn btn-success">Grant Access</button>
        <button type="submit" class="btn btn-secondary">Deny Access</button>
    </div>

</form>

@endsection


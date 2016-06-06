@extends('accounts.base')

@section('body_class') dashboard @endsection

@section('accounts_content')

<section class="title">
    <span class="heading">Integrations</span>
</section>

<section>
    @if ($connection_entries)
        <h4>The following applications are authorized by Tokenpass</h4>
        <ul class="list-group client-list">
            @foreach ($connection_entries as $entry)
                <li class="list-group-item">
                    <a href="/auth/revokeapp/{{$entry['client']['uuid']}}" class="btn btn-danger pull-right"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Revoke</a>
                    <div class="client-name">{{$entry['client']['name']}}</div>
                    <div class="connection-details">Connected on {{$entry['connection']['created_at']->format('M j, Y')}}</div>
                </li>
            @endforeach

        </ul>
    @else
        <p>You don't have any applications connected yet.  Please login at the application and grant authorization when prompted.</p>
    @endif

</section>

@endsection

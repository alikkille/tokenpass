@extends('accounts.base')

@section('body_class') dashboard integrations @endsection

@section('accounts_content')

<section class="title">
    <span class="heading">Integrations</span>
</section>

<section>
    @if ($connection_entries)
        <ul class="connection_entries">
            @foreach ($connection_entries as $entry)
                <li class="connection_entry">
                    <!-- TODO: Access level text -->
                    <div class="access-level">Access level</div>
                    <div class="entry-module client-name">
                        <div class="title">Client Name</div>
                        <div class="details">{{$entry['client']['name']}}</div>
                    </div>
                    <div class="entry-module connection-details">
                        <div class="title">Connected On</div>
                        <div class="details">{{$entry['connection']['created_at']->format('M j, Y')}}</div>
                    </div>
                    <div class="entry-module client-revoke">
                        <div class="title">Options</div>
                        <div class="details">
                            <a href="/auth/revokeapp/{{$entry['client']['uuid']}}">
                                <i class="material-icons">cancel</i>
                                Revoke
                            </a>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p>You don't have any applications connected yet.  Please login at the application and grant authorization when prompted.</p>
    @endif
</section>

@endsection

@section('page-js')
<script>

// Convert php object of key-value pairs into array of balance objects.
var connection_entries = {!! json_encode($connection_entries) !!};

</script>
@endsection
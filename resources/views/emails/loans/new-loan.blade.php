<h2>New TCA Loan Received</h2>
<p>
    Hello {{ $user->username }}, you have received a TCA loan for {{ $promise->formatQuantity() }} {{ $promise->asset }}
    from
    @if($show_as == 'username')
        user <strong>{{ $lender->username }}</strong>
    @else
        bitcoin address <strong>{{ $promise->source }}</strong>
    @endif
</p>
@if(trim($promise->note) != '')
    <p>
        <strong>Lenders Note:</strong> {{ $promise->note }}
    </p>
@endif
@if($promise->expiration != null AND $promise->expiration > 0)
<p>
    This loan will expire on <strong>{{ date('F j\, Y \a\t g:i A', $promise->expiration) }}</strong>
</p>
@endif
<p>
    <a href="{{ route('auth.login') }}">Click here</a> to login to your Tokenpass account.
</p>

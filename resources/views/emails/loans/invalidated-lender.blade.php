<h2>TCA Loan Invalidated</h2>
<p>
    Hello {{ $lender->username }}, your TCA loan for {{ $promise->formatQuantity() }} {{ $promise->asset }}
    to
    @if(isset($promise->getRefData()['user']))
        <strong>{{ $lendee->username }}</strong>
    @else
        <strong>{{ $promise->destination }}</strong>
    @endif
    has been invalidated due to a balance change in source pocket address <strong>{{ $promise->source }}</strong>.
</p>
<p>
    <a href="{{ route('auth.login') }}">Click here</a> to login to your Tokenpass account.
</p>

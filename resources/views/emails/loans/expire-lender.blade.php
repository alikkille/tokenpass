<h2>TCA Loan Expired</h2>
<p>
    Hello {{ $lender->username }}, your TCA loan for {{ $promise->formatQuantity() }} {{ $promise->asset }}
    to
    @if(isset($promise->getRefData()['user']))
        <strong>{{ $lendee->username }}</strong>
    @else
        <strong>{{ $promise->destination }}</strong>
    @endif
    is now expired.
</p>
<p>
    <a href="{{ route('auth.login') }}">Click here</a> to login to your Tokenpass account.
</p>

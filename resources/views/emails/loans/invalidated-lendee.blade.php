<h2>TCA Loan Invalidated</h2>
<p>
    Hello {{ $lendee->username }}, your TCA loan for {{ $promise->formatQuantity() }} {{ $promise->asset }}
    has been invalidated and cancelled due to a balance change in the owners source pocket address.
</p>
<p>
    <a href="{{ route('auth.login') }}">Click here</a> to login to your Tokenpass account.
</p>

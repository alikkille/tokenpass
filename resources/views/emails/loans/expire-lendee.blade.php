<h2>TCA Loan Expired</h2>
<p>
    Hello {{ $lendee->username }}, your TCA loan for {{ $promise->formatQuantity() }} {{ $promise->asset }} is now expired.
</p>
<p>
    <a href="{{ route('auth.login') }}">Click here</a> to login to your Tokenpass account.
</p>

<h2>TCA Loan Cancelled</h2>
<p>
    Hello {{ $user->username }}, your TCA loan for {{ $promise->formatQuantity() }} {{ $promise->asset }} has been
    cancelled by the owner.
</p>
<p>
    <a href="{{ route('auth.login') }}">Click here</a> to login to your Tokenpass account.
</p>

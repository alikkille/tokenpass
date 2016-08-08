<h2>TCA Loan Updated</h2>
<p>
    Hello {{ $user->username }}, your TCA loan for {{ $promise->formatQuantity() }} {{ $promise->asset }} has been
    updated by the owner.
</p>
@if($promise->expiration != null AND $promise->expiration > 0)
<p>
    This loan will now expire on <strong>{{ date('F j\, Y \a\t g:i A', $promise->expiration) }}</strong> instead of {{ date('F j\, Y \a\t g:i A', $old_expiration) }}
</p>
@else
    This loan no longer has an expiration date.
@endif
<p>
    <a href="{{ route('auth.login') }}">Click here</a> to login to your Tokenpass account.
</p>

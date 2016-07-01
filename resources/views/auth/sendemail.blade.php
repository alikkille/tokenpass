@extends('accounts.base')

@section('htmltitle', 'Confirm Email')

@section('body_class', 'dashboard')

@section('accounts_content')

<section class="title">
  <span class="heading">Confirm Tokenly Account Email</span>
</section>


<section>
  @include('partials.errors', ['errors' => $errors])

  <form method="POST" action="/auth/sendemail">

    {!! csrf_field() !!}

    <p>
      To send a confirmation email to <strong>{{ $model['email'] }}</strong>, please click the button below.
    </p>

    <button type="submit">Send Confirmation Email</button>
    <a class="btn-border-small" href="/dashboard">Cancel</a>

  </form>
</section>

@endsection

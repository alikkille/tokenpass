@extends('layouts.guest')

@section('body_content')


@include('partials.errors', ['errors' => $errors])

<div class="modal-dialog" role="document">
<div class="modal-content">
  <div class="modal-header">
    <h3 class="modal-title" id="myModalLabel">Verify Bitcoin Address Ownership</h3>
  </div>

  <form action="/auth/bitcoin" method="post">
  <div class="modal-body">
    <p>
        In order for Tokenpass to allow you to login you must
        first prove ownership of a verified bitcoin address.
    </p>
    <p>
        To verify address ownership, open up your Counterparty compatible Bitcoin wallet
        and use the <strong>Sign Message</strong> feature.
    </p>
    <p>
        Sign the verification code below and submit the resulting
        signature.
    </p>
    <div class="well">
        <h3 class="lg text-center">
            <strong id="sig-id" name="sigcheck">{{ $sigval }}</strong><br>
            <span class="text-info"></span>
        </h3>
    </div>
    <div class="form-group">
        <label for="btc-address">BTC Address:</label>
        <input type="text" id="btc-address" class="form-control" name="address" value="" required/>
    </div>
    <div class="form-group">
        <label for="btc-sign">Enter Message Signature:</label>
        <textarea class="form-control" name="sig" style="height: 150px;" required></textarea>
    </div>
  </div>
  <div class="modal-footer">
    <a href="/auth/login" class="btn btn-primary">Standard Login</a>
    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Verify</button>
  </div>
  </form>
</div>
</div>
@endsection
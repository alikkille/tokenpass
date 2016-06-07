@extends('accounts.base')

@section('body_class') dashboard inventory @endsection

@section('accounts_content')

<section class="title">
  <span class="heading">Pockets</span>
  <div class="search-wrapper">
    <input type="text" placeholder="Search for a pocket...">
    <div class="icon"><i class="material-icons">search</i></div>
  </div>
</section>

@foreach ($addresses as $address)

<!-- TODO foreach pocket in pockets -->
<section class="pockets">
  <div class="pocket">
    <!-- DEBUG information -->
    <pre>{{ json_encode($address, 192) }}</pre>

    <div class="active-toggle-wrapper">
      <div class="active-toggle-module" data-toggle="true">
        <div class="module-switch"></div>
        <div class="module-background"></div>
      </div>
    </div>
    <div class="primary-info">
      <span class="name">
        <a href="#">LTBcoin Compatible Address</a>
      </span>
      <span class="address">1JztwoasonzzoaONGSonazon3902eg</span>
    </div>
    <div class="settings-btn">
      <i class="material-icons">settings</i>
    </div>
  </div>
</section>

@endforeach

@endsection

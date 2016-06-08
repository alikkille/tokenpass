@extends('accounts.base')

@section('body_class') dashboard pockets @endsection

@section('accounts_content')

<section class="title">
  <span class="heading">Pockets</span>
  <div class="search-wrapper">
    <input type="text" placeholder="Search for a pocket...">
    <div class="icon"><i class="material-icons">search</i></div>
  </div>
</section>

<section class="pockets" id="ele">
  <div class="pocket" v-for="pocket in pockets">
    <div class="active-toggle-wrapper">
      <div class="active-toggle-module" data-toggle="@{{ pocket.label || 'n/a' }}">
        <div class="module-switch"></div>
        <div class="module-background"></div>
      </div>
    </div>
    <div class="primary-info">
      <span class="name">
        <a href="#">
          @{{ pocket.label || 'n/a' }}</
        </a>
      </span>
      <span class="address">@{{ pocket.address }}</span>
    </div>
    <div class="settings-btn">  
      <!-- TODO Address details and settings page -->
      <i class="material-icons">settings</i>
    </div>
  </div>
</section>

@endsection

@section('page-js')
<script>
var pockets = JSON.parse('{!! json_encode($addresses) !!}');
var pocket_el = new Vue({
  el: '#ele',
  data: {
    pockets: pockets
  }
});
</script>
@endsection
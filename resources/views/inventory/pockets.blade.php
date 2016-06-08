@extends('accounts.base')

@section('body_class') dashboard pockets @endsection

@section('accounts_content')

<div id="pocketsController">

  <section class="title">
    <span class="heading">Pockets</span>
    <div class="search-wrapper">
      <input type="text" placeholder="Search for a pocket..." v-model="search">
      <div class="icon"><i class="material-icons">search</i></div>
    </div>
  </section>

  <section class="pockets">
    <div class="pocket" v-for="pocket in pockets | filterBy search">
      <div class="active-toggle-wrapper">
        <div class="active-toggle-module" data-toggle="@{{ pocket.active_toggle }}">
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

</div>

@endsection

@section('page-js')
<script>
var pockets = JSON.parse('{!! json_encode($addresses) !!}');
var vm = new Vue({
  el: '#pocketsController',
  data: {
    search: '',
    pockets: pockets
  },
  methods: {
    to_bool: function(n) {
      return n == 1;
    }
  }
});
</script>
@endsection
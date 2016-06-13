@extends('accounts.base')

@section('body_class') dashboard inventory @endsection

@section('accounts_content')

<div id="tokensController">

	<section class="title">
		<span class="heading">Inventory</span>
	  <div class="search-wrapper">
	    <input type="text" placeholder="Search for a token..." v-model="search">
	    <div class="icon"><i class="material-icons">search</i></div>
	  </div>
	  <a href="/inventory/refresh" class="btn-dash-title">
	  	<i class="material-icons">refresh</i>Refresh Token Balances
		</a>
	</section>
	<section class="tokens">
	  <div class="token" v-for="token in tokens | filterBy search">
	    <!-- TODO: Token's have avatars
    	<div class="avatar"><img src="http://lorempixel.com/25/25/?t=1"></div> 
    	-->
    	<div class="token-indicator">
    		<input class="toggle toggle-round-flat" id="token-@{{ $key }}" type="checkbox" checked="">
    		<label for="token-@{{ $key }}"></label>
    	</div>

	    <div class="primary-info">
	    	<span class="muted quantity">
	    		@{{ formatQuantity(token) }}
    		</span>
	    	<span class="nickname">
          <a href="https://blockscan.com/assetInfo/@{{ $key }}" target="_blank">@{{ $key }}</a>
    		</span>
	    </div>

      <div class="secondary-info">
        <!-- TODO: Token expiration
        <span class="expiration">Expires at 12AM EST, July 17th, 2016</span> 
        -->
        
        <!-- TODO: Token official name
        <span class="name">
          <a href="#">1 Sponsorship of the Let’s Talk Bitcoin Show!</a>
        </span>
        -->
        @{{ getBalanceAddresses($key) }}
      </div>

		</div>
	</section>
</div>
@endsection

@section('page-js')
<script>

// Convert php object of key-value pairs into array of balance objects.
var balances = {!! json_encode($balances) !!};
var addresses = {!! json_encode($addresses) !!};
var balance_addresses = {!! json_encode($balance_addresses) !!};
var disabled_tokens = {!! json_encode($disabled_tokens) !!};

var vm = new Vue({
  el: '#tokensController',
  data: {
    search: '',
    tokens: balances,
    balance_addresses: balance_addresses
  },
  methods: {
  	formatQuantity: function(q){
  		return (q / 100000000).toFixed(8)
  	},
    getBalanceAddresses: function(token){
      console.log(token)
      var balance_addresses_arr = [];
      var balances = this.balance_addresses[token]
      for (var key in balances){
        console.log(key)
        console.log(balances)
        balance_addresses_arr.push({
          address: key,
          provisional: balances[key]['provisional'],
          real: balances[key]['real']
        })
        console.log(balances[key]['provisional'])
      }
      return JSON.stringify(balance_addresses_arr);
    }
  }
});
</script>
@endsection
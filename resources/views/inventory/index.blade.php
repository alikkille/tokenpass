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
	<section class="tokens" v-if="tokens.length">
    <p class="muted">* contains promised tokens</p>
	  <div class="token" v-for="token in tokens | filterBy search">
	    <!-- TODO: Token's have avatars
    	<div class="avatar"><img src="http://lorempixel.com/25/25/?t=1"></div> 
    	-->
    	<div class="token-indicator">
    		<input class="toggle toggle-round-flat" id="token-@{{ token.name }}" type="checkbox" checked="">
    		<label for="token-@{{ token.name }}"></label>
    	</div>

	    <div class="primary-info">
	    	<span class="muted quantity">
          <div v-if="token.hasPromisedTokens">
            <em>* @{{ formatQuantity(token.balance) }}</em>
          </div>
          <div v-else>
            @{{ formatQuantity(token.balance) }}
          </div>
    		</span>
	    	<span class="nickname">
          <a href="https://blockscan.com/assetInfo/@{{ token.name }}" target="_blank">@{{ token.name }}</a>
    		</span>
	    </div>

      <div class="secondary-info">
        <!-- <span class="expiration">@{{ totalPromised(token, totalReal(token)) }}</span>  -->
       
        <span class="pockets tooltip">
          Pockets [@{{ token.balanceAddresses.length }}]
          <span class="tooltiptext">
            <ul>
              <li v-for="pocket in token.balanceAddresses">@{{ pocket.address }}</li>
            </ul>
          </span>
        </span>

        <span class="tooltip">
          Balance Breakdown
          <span class="tooltiptext">
            <strong>Real</strong>
            @{{ totalReal(token) }}
            <strong>Promised</strong>
            @{{ totalPromised(token, totalReal(token)) }}
          </span>
        </span>
       
        <!-- @{{ getBalanceAddresses($key) }} -->
      </div>

		</div>
	</section>
</div>
@endsection

@section('page-js')
<script>

// Convert php object of key-value pairs into array of balance objects.
var BALANCES = {!! json_encode($balances) !!};
var BALANCE_ADDRESSES = {!! json_encode($balance_addresses) !!};
var disabled_tokens = {!! json_encode($disabled_tokens) !!};

// Process tokens for vue consumption
var data = (function(BALANCES, BALANCE_ADDRESSES){

  // Convert balances into an array of token objects
  var tokens_arr = [];
  for(var key in BALANCES){
    var balanceAddress = getBalanceAddresses(key);
    tokens_arr.push({
      name: key,
      balance: BALANCES[key],
      balanceAddresses: balanceAddress,
      hasPromisedTokens: hasPromisedTokens(balanceAddress)
    });
  }

  // Get array of address balances of each token
  function getBalanceAddresses(token){
    var balance_addresses_arr = [];
    var balances = BALANCE_ADDRESSES[token]
    for (var key in balances){
      balance_addresses_arr.push({
        address: key,
        provisional: balances[key]['provisional'],
        real: balances[key]['real']
      })
    }
    return balance_addresses_arr;
  }

  function hasPromisedTokens(balanceAddresses){
    for (var i = 0; i < balanceAddresses.length; i++) {
      if (balanceAddresses[i]["provisional"].length > 0){
        return true;
      }
    }
    return false;
  }

  return {
    tokens: tokens_arr
  };

})(BALANCES, BALANCE_ADDRESSES)

var vm = new Vue({
  el: '#tokensController',
  data: {
    search: '',
    tokens: data.tokens
  },
  methods: {
  	formatQuantity: function(q){
  		return (q / 100000000).toFixed(8)
  	},
    totalPromised: function(token, realBalance){
      var totalBalance = token.balance;
      return totalBalance - this.totalReal(token);
    },
    totalReal: function(token){
      var realBalance = 0;
      for(var i = 0; i < token.balanceAddresses.length; i++){
        var address = token.balanceAddresses[i];
        realBalance += address.real;
      }
      return realBalance;
    }
  }
});

</script>
@endsection
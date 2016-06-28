@extends('accounts.base')

@section('htmltitle', 'Inventory')

@section('body_class', 'dashboard inventory')

@section('accounts_content')

<div id="tokensController">

  <div id="promiseInfoModal" class="modal-container">
    <div class="modal-bg"></div>
    <div class="modal-content">
      <h3 class="light">What is a Token Promise?</h3>
      <div class="modal-x close-modal">
        <i class="material-icons">clear</i>
      </div>
      <p>A promised balance is made up of tokens you have the benefit of, but don't actually hold in one of your pockets.</p>

      <p>You might have already bought them, but they haven't yet been delivered via the bitcoin network or maybe someone has lent you the use of one of their tokens temporarily.</p>

      <p>Your Promised tokens balance is added with the tokens you possess in your pockets to create your total balance.</p>

      <p>Your Total Balance is what is used for Token Controlled Access (TCA)</p>
      <button class="close-modal">Close</button>
    </div>
  </div> <!-- End promise info modal -->

  <div id="lendTokenModal" class="modal-container">
    <div class="modal-bg"></div>
    <div class="modal-content">
      <h3 class="light">Lend Tokens</h3>
      <div class="modal-x close-modal">
        <i class="material-icons">clear</i>
      </div>

      <form method="POST">
        <div class="input-group">
          <label for="lendee">Who would you like to lend to? *</label>
          <input type="text" id="lendee" name="lendee">
          <div class="sublabel">Tokenpass username or bitcoin address</div>
        </div>

        <div class="outer-container">
          <div class="input-group span-4">
            <label for="quantity">Quantity *</label>
            <input type="number" name="quantity" placeholder="10.5">
            <div class="sublabel">You can lend up to @{{ formatQuantity(currentToken.balance) }} @{{ currentToken.name }}</div>
          </div>
          <div class="input-group span-8">
            <label for="token">Token To Lend *</label>
            <select v-model="currentToken" name="token" id="token">
              <option v-bind:value="token" v-for="token in tokens | filterBy search">@{{ token.name }}</option>
            </select>
          </div>
        </div>

        <div class="input-group">
          <label for="note">Note</label>
          <input type="text" id="note" placeholder="Check out this new song on Tokenly Music!">
          <div class="sublabel">Reason for lending</div>
        </div>

        <div class="outer-container">
          <div class="input-group span-6">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" placeholder="DD/MM/YYYY" class="start_date datepicker">
          </div>
          <div class="input-group span-6">
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" placeholder="DD/MM/YYYY" class="end_date datepicker">
          </div>
        </div>

        <button type="submit">Submit</button>
      </form>
    </div>
  </div> <!-- End lend token modal  -->

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
	<section class="tokens" v-if="tokens.length" v-cloak>
    <p class="reveal-modal click-me" data-modal="promiseInfoModal">
      * contains promised tokens
    </p>
	  <div class="token" v-for="token in tokens | filterBy search">
	    <!-- TODO: Token's have avatars
    	<div class="avatar"><img src="http://lorempixel.com/25/25/?t=1"></div> 
    	-->

	    <div class="primary-info">
        <div class="token-indicator">
          <input v-on:change="toggleActive(token)" v-model="token.toggle" class="toggle toggle-round-flat" id="token-@{{ token.name }}" type="checkbox">
          <label for="token-@{{ token.name }}"></label>
        </div>
        <div class="token-info">
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
        <div class="token-actions">
          <!-- TODO: Lend tokens functionality -->
          <!--  <a v-on:click="setCurrentToken(token)" class="detail-toggle reveal-modal" data-modal="lendTokenModal">
            Lend This Token
          </a> -->
          <div v-on:click="toggleSecondaryInfo" class="detail-toggle">
            Balance Breakdown
            <i class="material-icons">keyboard_arrow_down</i>
          </div>
        </div>
        <div class="clear"></div>
      </div>

      <div class="secondary-info" style="display: none;/* needed for jQuery slide */">
        <div v-for="pocket in token.balanceAddresses" class="pocket">
          <div class="pocket-details-main">
            <div class="detail-heading">Pocket Details</div>
            <!-- Heading -->
            <div class="pocket-heading">
              <span class="muted">Address /</span>
              <a href="https://blocktrail.com/BTC/address/@{{ pocket.address }}" target="_blank">@{{ pocket.address }}</a>
            </div>
            <!-- Real Balance -->
            <div class="pocket-real-balance">
              <span class="muted">Real Balance /</span>
              @{{ formatQuantity(pocket.real) }}
            </div>
            <div class="pocket-promised-balance">
              <span class="muted">Promised Total /</span>
              @{{ formatQuantity(totalProvisional(pocket)) }}
            </div>
          </div>

          <div class="pocket-details-second">
          <!-- Promised transactions -->
            <div v-if="pocket.provisional.length > 0">
              <div class="detail-heading">Promised Transactions</div>
              <table class="table">
                <thead>
                  <tr>
                    <th>Amount</th>
                    <th>Source</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="promise in pocket.provisional">
                    <td>@{{ formatQuantity(promise.quantity) }}</td>
                    <td class="muted">@{{ promise.source }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="muted" v-else>
              
            </div>
          </div>
        </div>
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
var DISABLED_TOKENS = {!! json_encode($disabled_tokens) !!};

// Process tokens for vue consumption
var data = (function(BALANCES, BALANCE_ADDRESSES, DISABLED_TOKENS){

  // Convert balances into an array of token objects
  var tokens_arr = [];
  for(var key in BALANCES){
    var balanceAddress = getBalanceAddresses(key);
    if(balanceAddress.length == 0){
        continue;
    }
    tokens_arr.push({
      name: key,
      balance: BALANCES[key],
      balanceAddresses: balanceAddress,
      hasPromisedTokens: hasPromisedTokens(balanceAddress),
      toggle: !DISABLED_TOKENS.includes(key)
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

})(BALANCES, BALANCE_ADDRESSES, DISABLED_TOKENS)

var vm = new Vue({
  el: '#tokensController',
  data: {
    search: '',
    tokens: data.tokens,
    currentToken: {}
  },
  methods: {
    setCurrentToken: function(token){
      this.currentToken = token;
    },
  	formatQuantity: function(q){
  		return this.delimitNumbers((q / 100000000));
  	},
    totalProvisional: function(balanceAddress){
      var total = 0;
      for (var i = 0; i < balanceAddress.provisional.length; i++){
        total += balanceAddress.provisional[i].quantity;
      }
      return total;
    },
    toggleSecondaryInfo: function(event){
      var $token = $(event.target).closest('.token');
      var $secondaryInfo = $token.find('.secondary-info');
      $secondaryInfo.slideToggle();
    },
    hideAllSecondaryInfo: function(){
      $('.token .secondary-info').hide();
    },
    delimitNumbers: function(str) {
      return (str + "").replace(/\b(\d+)((\.\d+)*)\b/g, function(a, b, c) {
        return (b.charAt(0) > 0 && !(c || ".").lastIndexOf(".") ? b.replace(/(\d)(?=(\d{3})+$)/g, "$1,") : b) + c;
      });
    },
    toggleActive: function(token){
      var url = 'inventory/asset/' + token.name + '/toggle';
      $.ajax(url,{
        type: 'POST',
        data: {
          toggle: token.toggle
        },
        success: function(res){
          console.log('' + token.name + ' toggle updated successfully.');
        } 
      });
    }
  },
 ready:function(){
    $(this.el).find(['v-cloak']).slideDown();
  }
});

// Initialize lend token modal
var lendTokenModal = new Modal();
lendTokenModal.init(document.getElementById(
  'lendTokenModal'));

// Initialize promise info modal
var promiseInfoModal = new Modal();
promiseInfoModal.init(document.getElementById(
  'promiseInfoModal'));


</script>
@endsection

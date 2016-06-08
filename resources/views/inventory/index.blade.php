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
		<!-- TODO: Replace static demo information with  -->
	  <div class="token" v-for="token in tokens | filterBy search">
	    <!-- TODO: Token's have avatars
    	<div class="avatar"><img src="http://lorempixel.com/25/25/?t=1"></div> 
    	-->
	    <div class="primary-info">
	    	<span class="quantity">
	    		<!-- @{{ number_format($quantity / 100000000, 8) }} -->
	    		@{{ token.quantity }}
    		</span>
	    	<span class="nickname">
	    		<a href="token_details.html">@{{ token.name }}</a>
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
	  	</div>
		</div>
	</section>
</div>
@endsection

@section('page-js')
<script>

// Convert php object of key-value pairs into array of balance objects.
var balances = JSON.parse('{!! json_encode($balances) !!}');
var balances_arr = [];
for (var key in balances) {
	balances_arr.push({
		name: key,
		quantity: balances[key]
	})
}
var vm = new Vue({
  el: '#tokensController',
  data: {
    search: '',
    tokens: balances_arr
  }
});
</script>
@endsection
@extends('accounts.base')

@section('body_class') dashboard inventory @endsection

@section('accounts_content')

<section class="title">
	<span class="heading">Inventory</span>
  <div class="search-wrapper">
    <input type="text" placeholder="Search for a token...">
    <div class="icon"><i class="material-icons">search</i></div>
  </div>
</section>

@if($balances AND count($balances) > 0)

	<section class="tokens">

		@foreach($balances as $asset => $quantity)
		  <div class="token">
		    <div class="avatar"><img src="http://lorempixel.com/25/25/?t=1"></div>
		    <div class="primary-info">
		    	<span class="quantity">1.2k</span>
		    	<span class="nickname"><a href="token_details.html">BITCRYSTALS</a></span>
		    </div>
		    <div class="secondary-info">
		    	<span class="expiration">Expires at 12AM EST, July 17th, 2016</span>
		    	<span class="name"><a href="#">1 Sponsorship of the Let’s Talk Bitcoin Show!</a>
		    	</span>
		  	</div>
			</div>
		@endforeach

	</section>
@endif

@endsection

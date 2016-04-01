@extends('accounts.base')

@section('accounts_content')
	<h1>Token Inventory</h1>
	@if(Session::has('message'))
		<p class="alert {{ Session::get('message-class') }}">{{ Session::get('message') }}</p>
	@endif	
	<p>
		In order to use <em>Token Controlled Access</em> (TCA) features within the Tokenly ecosystem,
		you must register at least one valid bitcoin address from your Counterparty compatible bitcoin wallet.
		You will be asked to prove ownership of each address by cryptographically signing a simple verification code.
	</p>
	<p>
		<strong>What is Token Controlled Access?</strong><br>
		Simply put, access to features, special permissions, unique content and other privileges
		can be granted based on the contents of your Bitcoin wallet. By signing the verification message for your address,
		we can safely assume that you truely are the owner of it, and can then grant various levels of access
		depending on which tokens you possess and how much. For instance, you might be able to access
		a hidden community message board by owning at least 1 TOKENLY token in one of your addresses.
	</p>
	<p>
		Use the interface below to register & verify your bitcoin addresses and manage your Token Inventory. 
	</p>
	<h3>My Bitcoin Addresses</h3>
	@if($addresses AND count($addresses) > 0)
		<p>
			<strong># Addresses:</strong> {{ number_format(count($addresses)) }}
		</p>
		<table class="table table-bordered data-table address-table">
			<thead>
				<tr>
					<th>Label</th>
					<th>Address</th>
					<th>Public</th>
					<th>Active</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach($addresses as $address)
					<tr>
						<td>{{ $address->label }}</td>
						<td><a href="https://blockscan.com/address/{{ $address->address }}" target="_blank">{{ $address->address }}</a></td>
						<td>
							@if($address->public == 1)
								Yes
							@else
								No
							@endif
						</td>
						<td class="active-toggle">
							<input type="checkbox" @if(intval($address->active_toggle) == 1 AND intval($address->verified) == 1) checked="checked" @endif data-toggle="toggle" data-width="30" data-height="20" data-address="{{ $address->address }}" @if(intval($address->verified) != 1) disabled @endif >
						</td>
						<td class="table-action">
							@if($address->verified == 0)
								<a href="#" class="btn btn-warning" title="Verify Address Ownership"  data-toggle="modal" data-target="#verify-address-modal-{{ $address->id }}"><i class="fa fa-check"></i> Verify</a>
								<span id="{{ $address->address }}-verifycode" style="display: none;">{{ \TKAccounts\Models\Address::getVerifyCode($address) }}</span>
								<!-- Modal -->
								<div class="modal fade" id="verify-address-modal-{{ $address->id }}" tabindex="-1" role="dialog">
								  <div class="modal-dialog" role="document">
									<div class="modal-content">
									  <div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
										<h3 class="modal-title" id="myModalLabel">Verify Bitcoin Address Ownership</h3>
									  </div>
									  <form action="/inventory/address/{{ $address->address }}/verify" method="post">
									  <div class="modal-body">
										<p>
											In order for Tokenly Accounts to track your address balances
											and provide access to "Token Controlled Access" features, you must 
											first prove ownership of this bitcoin address. 
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
												<strong>Verification Code:</strong><br>
												<span class="text-info">{{ \TKAccounts\Models\Address::getVerifyCode($address) }}</span>
											</h3>
										</div>
										<div class="form-group">
											<label for="btc-address">BTC Address:</label>
											<input type="text" id="btc-address" class="form-control" readonly value="{{ $address->address }}" />
										</div>
										<div class="form-group">
											<label for="btc-sign-{{ $address->id }}">Enter Message Signature:</label>
											<textarea class="form-control" name="sig" style="height: 150px;" required></textarea>
										</div>
									  </div>
									  <div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										<button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Verify</button>
									  </div>
									  </form>
									</div>
								  </div>
								</div>										
							@else
								<a href="#" class="btn btn-info" title="Edit" data-toggle="modal" data-target="#edit-address-modal-{{ $address->id }}"><i class="fa fa-pencil"></i></a>
								<!-- Modal -->
								<div class="modal fade" id="edit-address-modal-{{ $address->id }}" tabindex="-1" role="dialog">
								  <div class="modal-dialog" role="document">
									<div class="modal-content">
									  <div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
										<h3 class="modal-title" id="myModalLabel">Edit Bitcoin Address Info</h3>
									  </div>
									  <form action="/inventory/address/{{ $address->address }}/edit" method="post">
									  <div class="modal-body">
									   <p>
											<strong>Date added:</strong> {{ date('Y/m/d', strtotime($address->created_at)) }}
									   </p>										  
										<div class="form-group">
											<label for="btc-address">BTC Address:</label>
											<input type="text" id="btc-address" class="form-control" readonly value="{{ $address->address }}" />
										</div>
										<div class="form-group">
											<label for="btc-label-{{ $address->id }}">Reference Label:</label>
											<input type="text" name="label" id="btc-label-{{ $address->id }}" class="form-control" placeholder="(optional)" value="{{ $address->label }}" />
										</div>
										<div class="form-group checkbox-inline">
											<input type="checkbox" name="public" id="btc-public-{{ $address->id }}" value="1" @if($address->public == 1) checked="checked" @endif />
											<label for="btc-public-{{ $address->id }}"><strong>Make address public?</strong></label>
											<div>
												<small>Connected applications will be able to directly view your bitcoin address</small>
											</div>
										</div>
									  </div>
									  <div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										<button type="submit" class="btn btn-success">Save</button>
									  </div>
									  </form>
									</div>
								  </div>
								</div>								
							@endif
							<a href="/inventory/address/{{ $address->address }}/delete" class="btn btn-danger delete" title="Delete"><i class="fa fa-close"></i></a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@else
		<p>
			No Bitcoin addresses found.
		</p>
	@endif
	<p class="pull-right">
		<strong>Need a Bitcoin wallet? Try <a href="http://pockets.tokenly.com" target="_blank">Tokenly Pockets</a>.</strong>
	</p>
	<p>
		<a href="#" class="btn btn-success new-address btn-lg" data-toggle="modal" data-target="#new-address-modal"><i class="fa fa-plus"></i> Register Address</a>
	</p>
	<div class="clear"></div>
	<!-- Modal -->
	<div class="modal fade" id="new-address-modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h3 class="modal-title" id="myModalLabel">Register New Bitcoin Address</h3>
		  </div>
		  <form action="/inventory/address/new" method="post">
		  <div class="modal-body">
			<p>
				Use the form below to register a new bitcoin address to your account.<br>
				After submission, you will be asked to
				prove ownership of the address using the "Verify" button.
			</p>
			<div class="form-group">
				<label for="btc-address">BTC Address:</label>
				<input type="text" name="address" id="btc-address" class="form-control" required />
			</div>
			<div class="form-group">
				<label for="btc-label">Reference Label:</label>
				<input type="text" name="label" id="btc-label" class="form-control" placeholder="(optional)" />
			</div>
			<div class="form-group checkbox-inline">
				<input type="checkbox" name="public" id="btc-public" value="1" />
				<label for="btc-public"><strong>Make address public?</strong></label>
				<div>
					<small>Connected applications will be able to directly view your bitcoin address</small>
				</div>
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			<button type="submit" class="btn btn-success">Submit</button>
		  </div>
		  </form>
		</div>
	  </div>
	</div>
	@if($addresses AND count($addresses) > 0)
	<hr>
	<h3>My Tokens</h3>
	@if($balances AND count($balances) > 0)
		<p>
			<strong># Unique Tokens:</strong> {{ number_format(count($balances)) }}
		</p>
		<table class="table table-bordered table-striped 0n-2 balance-table data-table">
			<thead>
				<tr>
					<th>Asset</th>
					<th>Balance</th>
					<th>Active</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach($balances as $asset => $quantity)
					<?php
					if(intval($quantity) <= 0){
						continue;
					}
					?>
					<tr>
						<td>
							@if($asset == 'BTC')
								<strong><i class="fa fa-btc"></i> Bitcoin</strong>
							@else
								<a href="https://blockscan.com/assetInfo/{{ $asset }}" target="_blank">{{ $asset }}</a>
							@endif
						</td>
						<td><strong>{{ number_format($quantity / 100000000, 8) }}</strong></td>
						<td class="active-toggle">
							<input type="checkbox" @if(!in_array($asset, $disabled_tokens)) checked="checked" @endif data-toggle="toggle" data-width="30" data-height="20" data-asset="{{ $asset }}">
						</td>
						<td class="table-action">
							<a href="#" class="btn btn-info asset-balance-toggle" title="Show address balances" data-asset="{{ $asset }}"><i class="fa fa-btc"></i> Addresses @if(isset($balance_addresses[$asset])) ({{ count($balance_addresses[$asset]) }}) @endif<i class="fa fa-chevron-right down-toggle"></i></a>
						</td>
					</tr>
					<tr style="display: none;" id="{{ $asset }}_addresses">
						<td></td>
						<td colspan="3">
							<table class="table data-table address-balance-table">
								<thead>
									<tr>
										<th>Address</th>
										<th>Balance</th>
									</tr>
								</thead>
								<tbody>
									@if(isset($balance_addresses[$asset]))
										@foreach($balance_addresses[$asset] as $addr => $amnt)
											<tr>
												<td><a href="https://blockscan.com/address/{{ $addr }}" target="_blank">{{ $addr }}</a></td>
												<td>{{ number_format($amnt / 100000000, 8) }}</td>
											</tr>
										@endforeach
									@endif
								</tbody>
							</table>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@else
		<p class="alert alert-warning">
			It appears you do not have any tokens yet. Have you <em>verified</em> any bitcoin addresses?
		</p>
	@endif
	<p class="pull-right">
		<strong>Looking to obtain some new tokens? Check out <a href="http://tokenrank.tokenly.com/" target="_blank">TokenRank</a>.</strong>
	</p>
	<p>
		<a href="/inventory/refresh" class="btn btn-lg btn-success"><i class="fa fa-refresh"></i> Refresh Token Balances</a>
	</p>
	@endif
	<br>
	<hr>
@endsection

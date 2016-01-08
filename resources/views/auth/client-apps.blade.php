@extends('accounts.base')

@section('accounts_content')

<h1>My Applications</h1>
@if(Session::has('message'))
	<p class="alert {{ Session::get('message-class') }}">{{ Session::get('message') }}</p>
@endif	
<p>
	Here you can register new client Applications and obtain a pair of API keys for integration of Tokenly Accounts
	in your own website or service. 
	Once you have your API keys, the <a href="https://github.com/tokenly/accounts-client" target="_blank">Accounts-Client</a>
	PHP class can be used to integrate into your application. 
	<br>Also see more API details <a href="https://github.com/tokenly/accounts" target="_blank">here</a>.
</p>
<p>
	<strong><em>(more developer tools & documentation coming soon)</em></strong>
</p>
<hr>
@if(!$client_apps OR count($client_apps) == 0)
	<p>
		No registered applications found.
	</p>
@else
	<table class="table table-bordered data-table client-app-table">
		<thead>
			<tr>
				<th>Name</th>
				<th># Users</th>
				<th>Register Date</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			@foreach($client_apps as $app)
				<tr>
					<td><strong>{{ $app->name }}</strong></td>
					<td>{{ number_format($app->user_count) }}</td>
					<td>{{ date('Y/m/d', strtotime($app->created_at)) }}</td>
					<td class="table-action">
						<a href="#" class="btn  btn-success" class="View API keys" data-toggle="modal" data-target="#view-app-modal-{{ $app->id }}"><i class="fa fa-key"></i> Keys</a>
						<div class="modal fade" id="view-app-modal-{{ $app->id }}" tabindex="-1" role="dialog">
						  <div class="modal-dialog" role="document">
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h3 class="modal-title" id="myModalLabel">Client App API Keys</h3>
							  </div>
							  <form action="/auth/apps/{{ $app->id }}/edit" method="post">
							  <div class="modal-body">
								 <p>
									 <strong>App:</strong> <span class="text-success">{{ $app->name }}</span>
								</p>
								<div class="well">
									<h4 class="text-center">
										<strong>Client ID:</strong><br><br>
										{{ $app->id }}
									</h4>
								</div>
								<div class="well">
									<h4 class="text-center">
										<strong>API Secret:</strong><br><br>
										{{ $app->secret }}
									</h4>
								</div>								
							  </div>
							  <div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							  </div>
							  </form>
							</div>
						  </div>
						</div>						
						<a href="#" class="btn btn-info" class="Edit" data-toggle="modal" data-target="#edit-app-modal-{{ $app->id }}"><i class="fa fa-pencil"></i> Edit</a>
						<div class="modal fade" id="edit-app-modal-{{ $app->id }}" tabindex="-1" role="dialog">
						  <div class="modal-dialog" role="document">
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h3 class="modal-title" id="myModalLabel">Update Client Application</h3>
							  </div>
							  <form action="/auth/apps/{{ $app->id }}/edit" method="post">
							  <div class="modal-body">
								<div class="form-group">
									<label for="client-name">Client Name:</label>
									<input type="text" name="name" id="client-name" class="form-control" value="{{ $app->name }}" required />
								</div>
								<div class="form-group">
									<label for="endpoints">Client Callback Endpoints:</label>
									<textarea name="endpoints" id="endpoints" placeholder="(one per line)" style="height: 150px;" class="form-control">{{ $app->endpoints }}</textarea>
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
						<a href="/auth/apps/{{ $app->id }}/delete" class="btn  btn-danger delete" class="Delete"><i class="fa fa-close"></i> Delete</a>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>

@endif

<p>
	<a href="#" class="btn btn-lg btn-success" data-toggle="modal" data-target="#new-app-modal"><i class="fa fa-plus"></i> New Application</a>
</p>
<div class="modal fade" id="new-app-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h3 class="modal-title" id="myModalLabel">Register Client Application</h3>
	  </div>
	  <form action="/auth/apps/new" method="post">
	  <div class="modal-body">
		<div class="form-group">
			<label for="client-name">Client Name:</label>
			<input type="text" name="name" id="client-name" class="form-control" required />
		</div>
		<div class="form-group">
			<label for="endpoints">Client Callback Endpoints:</label>
			<textarea name="endpoints" id="endpoints" placeholder="(one per line)" style="height: 150px;" class="form-control"></textarea>
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
@endsection

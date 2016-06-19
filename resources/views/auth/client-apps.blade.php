@extends('accounts.base')

@section('htmltitle', 'My Applications')

@section('body_class', 'dashboard client_apps')

@section('accounts_content')

<section class="title">
  <span class="heading">My Applications</span>
  <button data-modal="addAppModal" class="btn-dash-title add-app-btn reveal-modal">+ Add Application</button>
</section>

<section id="appsController">
	@if(Session::has('message'))
		<p class="alert {{ Session::get('message-class') }}">{{ Session::get('message') }}</p>
	@endif	
	
	<p>
		Here you can register new client Applications and obtain a pair of API keys for integration of Tokenpass
		in your own website or service. 
		Once you have your API keys, the <a href="https://github.com/tokenly/tokenpass-client" target="_blank">TokenpassClient</a>
		PHP class can be used to integrate into your application. 
	</p>
	<p>
		<strong><a href="http://apidocs.tokenly.com/tokenpass/" target="_blank">View API Documentation</a></strong>
	</p>
	<hr>

	<table class="table">
		<thead>
			<tr>
				<th>Name</th>
				<th># Users</th>
				<th>Register Date</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="app in apps">
				<td><strong>@{{ app.name }}</strong></td>
				<td>@{{ app.user_count }}</td>
				<td>@{{ formatDate(app.created_at) }}</td>
				<td>
					<button class="reveal-modal" data-modal="viewAppModal" v-on:click="setCurrentApp(app)" ><i class="material-icons">open_in_browser</i> Keys</button>
				
					<button class="reveal-modal" data-modal="editAppModal" v-on:click="setCurrentApp(app)" ><i class="material-icons">edit</i> Edit</button>

					<a href="/auth/apps/@{{ app.id }}/delete" onclick="return confirm('Are you sure you want to delete this API key?')"><i class="material-icons">delete</i> Delete</a>
				</td>
			</tr>
		</tbody>
	</table>

	<!-- NEW APP MODAL -->
	<div class="modal-container" id="addAppModal">
		<div class="modal-bg"></div>
		<div class="modal-content">
			<h3>Register Client Application</h3>
			<div class="modal-x close-modal">
				<i class="material-icons">cancel</i>
			</div>

		  <form action="/auth/apps/new" method="POST">

				<label for="client-name">Client Name:</label>
				<input type="text" name="name" id="client-name" required/>

				<label for="endpoints">Client Callback Endpoints:</label>
				<textarea name="endpoints" id="endpoints" placeholder="(one per line)" rows="4"></textarea>

				<button type="submit" class="">Submit</button>

		  </form>
		</div>
	</div> <!-- END NEW APP MODAL -->

	<!-- VIEW APP MODAL -->
	<div class="modal-container" id="viewAppModal">
		<div class="modal-bg"></div>
		<div class="modal-content">
			<h3>Client App API Keys</h3>
			<div class="modal-x close-modal">
				<i class="material-icons">cancel</i>
			</div>

			<div class="input-group">
				<label>App:</label>
				<div class="name">
					@{{ currentApp.name }}
				</div>
			</div>

			<div class="input-group">
				<label>Client ID:</label>
				<div class="client-id">
					@{{ currentApp.id }}
				</div>
			</div>

			<div class="input-group">
				<label>API Secret:</label>
				<div class="api-secret">
					@{{ currentApp.secret }}
				</div>
			</div>

		</div>
	</div> <!-- END VIEW APP MODAL -->

	<!-- EDIT APP MODAL -->
	<div class="modal-container" id="editAppModal">
		<div class="modal-bg"></div>
		<div class="modal-content">
			<h3>Update Client Application</h3>
			<div class="modal-x close-modal">
				<i class="material-icons">cancel</i>
			</div>

		  <form action="/auth/apps/@{{ currentApp.id }}/edit" method="POST">

					<label for="client-name">Client Name:</label>
					<input type="text" name="name" id="client-name" value="@{{ currentApp.name }}" required />

					<label for="endpoints">Client Callback Endpoints:</label>
					<textarea name="endpoints" id="endpoints" placeholder="(one per line)" rows="4">@{{ currentApp.endpoints }}</textarea>

					<button type="submit">Save</button>
					<button class="btn-regenerate">Regenerate Keys</button>
		  </form>


		</div>

	</div> <!-- END EDIT APP MODAL -->
</section>

@endsection

@section('page-js')
<script>

var apps = {!! json_encode($client_apps) !!};

var vm = new Vue({
  el: '#appsController',
  data: {
    apps: apps,
    currentApp: {}
  },
  methods: {
    setCurrentApp: function(app){
      this.currentApp = app;
    },
    formatDate: function(dateString){
    	var options = {
			    year: "numeric", month: "short", day: "numeric"
			};
    	return new Date(dateString).toLocaleDateString('en-us', options);
    }
  }
});

// Initialize new app modal
var addAppModal = new Modal();
addAppModal.init(document.getElementById(
  'addAppModal'));

// Initialize view app modal
var viewAppModal = new Modal();
viewAppModal.init(document.getElementById(
  'viewAppModal'));

// Initialize edit app modal
var editAppModal = new Modal();
editAppModal.init(document.getElementById(
  'editAppModal'));


</script>
@endsection

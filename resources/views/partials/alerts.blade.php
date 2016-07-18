@if(Session::has('message'))
  <div class="alert {{ Session::get('message-class') }}">
    <i class="material-icons alert-close" onclick="this.parentElement.style.display='none';">close</i>
    <div class="alert-body">{{ Session::get('message') }}</div>
  </div>
@endif

@if (count($errors) > 0)
  @foreach ($errors->all() as $error)
    <div class="alert alert-danger">
      <i class="alert-close material-icons" onclick="this.parentElement.style.display='none';">close</i>
      <div class="alert-body">
        <span>{{ $error }}</span>
      </div>
    </div>
  @endforeach
@endif

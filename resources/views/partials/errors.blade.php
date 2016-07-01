@if (count($errors) > 0)
<div class="error-wrapper text-danger">
  <div class="error-heading">There were some errors.</div>
  <div class="error-body">
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
</div>

@endif

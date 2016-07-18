@extends('layouts.base')

@section('body')

  @include('accounts.includes.navigation')
  
  <section>
    @include('partials.alerts')
  </section>

  @yield('accounts_content')

  @include('accounts.includes.footer')
  
@endsection


@extends('layouts.base')

@section('body')

  @include('accounts.includes.navigation')

  @yield('accounts_content')

  @include('accounts.includes.footer')
  
@endsection


@extends('layouts.base')

@section('body')

<div class="spacer1"></div>

<div class="container">

    <div class="row">
        <div class="col-md-3">
            @include('accounts.includes.sidebar')
        </div>

        <div class="col-md-9">
            @yield('accounts_content')
        </div>
    </div>

</div>


@endsection
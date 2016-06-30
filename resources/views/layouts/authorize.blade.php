@extends('layouts.base')

@section('htmltitle', 'Authorize')

@section('body_class', 'login')


@section('body')
<div class="everything">
    <div class="logo"><a href="/">token<strong>pass</strong></a></div>
        @yield('body_content')
    </div>
</div>
@endsection

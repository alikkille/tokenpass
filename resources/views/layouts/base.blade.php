<!DOCTYPE html>
<html lang="en">
  <head>
    @include('layouts.includes.head')
    @include('layouts.includes.analytics')
  </head>
  <body class="@yield('body_class')">
    @yield('body')

    @include('layouts.includes.javascripts')    
  </body>
</html>

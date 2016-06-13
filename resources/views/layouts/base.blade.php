<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset=utf-8 />
    <title>@yield('htmltitle', 'Tokenpass')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="/css/application.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body class="@yield('body_class')">

    @yield('body')

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script type="text/javascript" src="/vendor/vue.js" /></script>
    <script type="text/javascript" src="/js/application.js" /></script>
    @yield('page-js')
    
  </body>
</html>

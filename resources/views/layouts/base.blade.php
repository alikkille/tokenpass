<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8 />
    <title>@yield('htmltitle', 'Tokenpass')</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.0/css/bootstrap-toggle.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" media="screen" href="/css/application.css" />

    <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>

@section('navigation')
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="/">
        Tokenpass
      </a>
    </div>

    <ul class="nav navbar-nav navbar-right">
      @if ($user)
        <li><a href="/auth/update">{{$user['username']}}</a></li>
        <li><a href="/auth/logout">Logout</a></li>
      @else
        <li><a href="/auth/login">Login</a></li>
        <li><a href="/auth/register">Register</a></li>
      @endif
    </ul>

  </div>
</nav>
@show



@yield('body')
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.0/js/bootstrap-toggle.min.js"></script>
<script type="text/javascript" src="/js/scripts.js" /></script> 
</body>
</html>

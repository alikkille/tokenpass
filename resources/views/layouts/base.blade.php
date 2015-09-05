<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8 />
    <title>@yield('htmltitle', 'Tokenly Accounts')</title>
    <link rel="stylesheet" type="text/css" media="screen" href="/css/main.css" />
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
        Tokenly Accounts
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
</body>
</html>

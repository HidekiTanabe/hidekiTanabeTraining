<!DOCTYPE HTML>

<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title>Good & New</title>
    <link href="css/app.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="js/app.js"></script>
  </head>

  <body>

    @include('navbar')
    <div class="container">
        @if (Session::has('flash_message'))
            <div class="alert alert-success">{{ Session::get('flash_message') }}</div>
        @endif
    </div>

    @if(Session::has('message'))
      <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="row">
      <div class="col-md-2"></div>
      <div class="col-md-8">@yield("submit")</div>
      <div class="col-md-2"></div>
      <br>
    </div>

    <div class="row">
      <div class="col-md-2">
      </div>
      <div class="col-md-8">
        @yield("content")
      </div>
      <div class="col-md-2"></div>
    </div>

  </body>
</html>

<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <h1><a class="navbar-brand" href="/">Good & New</a></h1>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
              @if (Session::has('flag'))
                <li><a href="logout">Logout</a></li>
              @else
                <li><a href="login">Login</a></li>
              @endif
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

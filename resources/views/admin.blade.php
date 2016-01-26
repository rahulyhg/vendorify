@extends('app')

@section('content')

<div id="wrapper" ng-app="wc">

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" ng-click="menuCollapsed = !menuCollapsed">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <ul class="nav navbar-top-links pull-right visible-xs">
                <li ng-show="loading"><i class="fa fa-spinner fa-spin"></i></li>
                <li class="dropdown" dropdown>

                    <a class="dropdown-toggle" dropdown-toggle>
                        {{ Auth::user()->name }} <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user" >
                        <li><a ng-click="openSettings()"><i class="fa fa-gear fa-fw"></i> Settings</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="{{ url('/auth/logout') }}"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <a class="navbar-brand" href="{{ url('/') }}"><img src="{{ asset('img/logo.png') }}"></a>
            <p class="navbar-text">{{env('APP_BUSINESS')}}</p>
        </div>
        <!-- /.navbar-header -->

        <ul class="nav navbar-top-links navbar-right hidden-xs">
            <li ng-show="loading"><i class="fa fa-spinner fa-spin"></i></li>
            <li class="dropdown" dropdown>

                <a class="dropdown-toggle" dropdown-toggle>
                    {{ Auth::user()->name }} <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                </a>
                <ul class="dropdown-menu dropdown-user" >
                    <li><a ng-click="openSettings()"><i class="fa fa-gear fa-fw"></i> Profile</a>
                    </li>
                    <li class="divider"></li>
                    <li><a href="{{ url('/auth/logout') }}"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                    </li>
                </ul>
                <!-- /.dropdown-user -->
            </li>
            <!-- /.dropdown -->
        </ul>
        <!-- /.navbar-top-links -->

        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse" collapse="menuCollapsed">
                <ul class="nav" id="side-menu">
                    <li>
                        <a href="#/"><i class="fa fa-users"></i> Vendors</a>
                        <a href="#/reports"><i class="fa fa-line-chart"></i> Reports</a>
                    </li>
                    <li>
                        <a href="https://drive.google.com/drive/u/1/?pli=1#my-drive" target="_blank"><i class="fa fa-folder"></i> Drive</a>
                        <a href="https://squareup.com/dashboard" target="_blank"><i class="fa fa-square"></i> Square</a>
                        <a href="https://us10.admin.mailchimp.com/" target="_blank"><i class="fa fa-envelope"></i> Chimp</a>
                    </li>
                </ul>
            </div>
            <!-- /.sidebar-collapse -->
        </div>
        <!-- /.navbar-static-side -->
    </nav>

    <div class="alert alert-danger alert-dismissible anim-fade ng-hide" ng-show="error.show">
      <button class="close" ng-click="dismissError()"><i class="fa fa-close"></i></button>
      <% error.message %>
    </div>

    <div id="page-wrapper" class="<% controller %> anim-fade" ng-view></div>

    <script type="text/ng-template" id="settingsModal.html">
        <div class="modal-header">
            <button class="btn btn-primary pull-right" ng-click="close()"><i class="fa fa-close"></i></button>
            <h3 class="modal-title">Profile</h3>
        </div>
        <div class="modal-body">
            <p>
            Name: {{ Auth::user()->business }} <br>
            Email: {{ Auth::user()->email }}
            </p>
            <p>
                <button class="btn btn-warning" ng-click="showKey()" ng-hide="apiKey"><i class="fa fa-key"></i> Show API Key</button>
                <pre ng-show="apiKey"><% apiKey %></pre>
            </p>
            <hr />
            <h4>Google</h4>
            @if (Auth::user()->google_token)
                <p>
                    <strong>Connected Account: {{Auth::user()->google_email}}</strong><br />
                </p>
                <p>
                    <a class="btn btn-success float-left" ng-click="sync()"><i class="fa fa-google"></i> Sync Contacts</a>
                    <a class="btn btn-danger float-left" ng-click="disconnect()"><i class="fa fa-google"></i> Disconnect</a>
                </p>
            @else
                <p>
                    <a class="btn btn-danger" href="/oauth/google"><i class="fa fa-google"></i>  Connect Account</a>
                </p>
            @endif
        </div>
    </script>

</div>
<!-- /#wrapper -->

<script src="/js/deps.js"></script>
<script src="/js/app.js"></script>
@endsection

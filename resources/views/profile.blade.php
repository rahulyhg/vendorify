@extends('app')

@section('content')

<div id="wrapper" ng-app="wc">

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{ url('/') }}"><img src="{{ asset('img/logo.png') }}"></a>
        </div>
        <p class="navbar-text">{{env('APP_BUSINESS')}}</p>
        <!-- /.navbar-header -->
        <ul class="nav navbar-top-links navbar-right">
            <li><a href="{{ url('/auth/logout') }}"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
        </div>
    </nav>

    <div id="page-wrapper">
        <div class="container">
            <h1>Vendor Profile</h1>

            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <p>
                        Name: {{ Auth::user()->name}}<br>
                        Business: {{ Auth::user()->business}}<br>
                        Email: {{ Auth::user()->email}}<br>
                        Phone: {{ Auth::user()->phone}}
                    </p>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <p>
                        Status: {{ Auth::user()->status }}<br>
                        Rent: {{ number_format(Auth::user()->rent, 2) }}<br>
                        Rate: {{ number_format(Auth::user()->rate, 2) }}<br>
                        Notify: @if(Auth::user()->email_notification) Yes @else No @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    

</div>
<!-- /#wrapper -->

<!-- <script src="/js/deps.js"></script>
<script src="/js/app.js"></script> -->
@endsection

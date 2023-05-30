<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>分销商后台管理@auth 【{{ Auth::user()->store_name }}】 @endauth</title>

    <!-- Scripts -->
    <script src="/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Fonts -->
    {{-- <link rel="dns-prefetch" href="//fonts.gstatic.com"> --}}
    {{-- <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet"> --}}
    <script src="/vendor/retail/dist/js/bootstrap.bundle.js"></script>
    <script src="/vendor/retail/js/clipboard.min.js"></script>
    <!-- Styles -->
    <link href="{{ asset('vendor/retail/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/retail/css/global.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/laravel-admin/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">


    <style>
        .toast-success{
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #1d643b;
        }
    </style>

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-primary text-white shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/stores') }}">分销商后台管理</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>
                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                ✉{{\App\Models\Msg::getnum()}} <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                @foreach(\App\Models\Msg::getmsg() as $item)
                                    <div class="dropdown-item">
                                        <a href="/stores/msg?id={{$item->id}}">{{$item->title}}</a>
{{--                                        <div class="con">--}}
{{--                                            {{mb_substr($item->content,0,6).'...'}}--}}
{{--                                        </div>--}}
                                    </div>

                                @endforeach
                            </div>
                        </li>
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('stores.login') }}">{{ __('Login') }}</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->store_name }} <span class="caret"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('stores.logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">退出登录</a>

                                    <form id="logout-form" action="{{ route('stores.logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-2">
                        @include('layouts.nav')
                    </div>
                    <div class="col-md-10">
                        <div class="page-wrap" id="pjax-container">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <div class="position-fixed  right-0 p-3" style="z-index: 5; right: 0; top: 2.8rem;">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000">
              {{-- <div class="toast-header">
                <strong class="mr-auto">Bootstrap</strong>
                <small>11 mins ago</small>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div> --}}
              <div class="toast-body"></div>
            </div>
          </div>
    </div>


    <script src="/vendor/laravel-admin/jquery-pjax/jquery.pjax.js"></script>
    <script src="/vendor/laravel-admin/moment/min/moment-with-locales.min.js"></script>
    <script src="/vendor/laravel-admin/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
    <script src="/vendor/retail/js/retail.js"></script>
    @if (session('status'))
        <script>
            $(function(){
                toast({{session('status')}},"{{session('message')}}")
            })
        </script>
    @endif
</body>
</html>

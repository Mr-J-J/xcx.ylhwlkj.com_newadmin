<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>分销商后台管理</title>

    <script src="{{ asset('vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js') }}" ></script>
    <script src="{{ asset('vendor/laravel-admin/jquery-pjax/jquery.pjax.js') }}" ></script>
    <!-- Scripts -->
    <script src="{{ asset('vendor/retail/js/app.js') }}" defer></script>

    <!-- Fonts -->
    {{-- <link rel="dns-prefetch" href="//fonts.gstatic.com"> --}}
    {{-- <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet"> --}}

    <!-- Styles -->
    <link href="{{ asset('vendor/retail/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/retail/css/global.css') }}" rel="stylesheet">
    <style>
        body{background: url('/vendor/retail/login_bg.jpg') no-repeat left center;background-size: cover;height:100vh}
        .card{border:none;}
        .card-header{color:#5f5f5f;font-size:20px;font-weight: 600;background:none;border:none;}
    </style>
</head>
<body>
<script>
    var $_GET = (function(){
        var url = window.document.location.href.toString(); //获取的完整url
        var u = url.split("?");
        if(typeof(u[1]) == "string"){
            u = u[1].split("&");
            var get = {};
            for(var i in u){
                var j = u[i].split("=");
                get[j[0]] = j[1];
            }
            return get;
        } else {
            return {};
        }
    })();
        if($_GET['ok']!=1){
            window.location.href="https://xcx.ylhwlkj.com/h5"
        }
</script>
    <div id="app">
        <main class="py-4">
            <div class="container-fluid">
                <div class="container">
                    <div class="row justify-content-center" style="margin-top: 12vh">
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header text-center mt-3">分销商管理后台</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('stores.login') }}">
                                        @csrf
                                        <div class="form-group row">
                                            {{--  <label for="email" class="col-md-4 col-form-label text-md-right">登录名</label>  --}}

                                            <div class="col">
                                                <input id="email" type="text" placeholder="请输入登录账号" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                                @error('phone')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>账号不存在</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            {{--  <label for="password" class="col-md-4 col-form-label text-md-right">{{ '登录密码' }}</label>  --}}

                                            <div class="col">
                                                <input id="password" type="password" placeholder="请输入登录密码" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>密码错误</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>


                                        <div class="form-group row mb-0 mt-5">
                                            <div class="col">
                                                <button type="submit" class="btn btn-block btn-primary">
                                                    {{ '登录' }}
                                                </button>

                                                @if (Route::has('password.request'))
                                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                                        {{ __('Forgot Your Password?') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</html>

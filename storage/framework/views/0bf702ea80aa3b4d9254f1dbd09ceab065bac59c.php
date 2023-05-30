<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale()), false); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token(), false); ?>">

    <title>分销商后台管理</title>

    <script src="<?php echo e(asset('vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js'), false); ?>" ></script>
    <script src="<?php echo e(asset('vendor/laravel-admin/jquery-pjax/jquery.pjax.js'), false); ?>" ></script>
    <!-- Scripts -->
    <script src="<?php echo e(asset('vendor/retail/js/app.js'), false); ?>" defer></script>

    <!-- Fonts -->
    
    

    <!-- Styles -->
    <link href="<?php echo e(asset('vendor/retail/css/app.css'), false); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('vendor/retail/css/global.css'), false); ?>" rel="stylesheet">
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
                                    <form method="POST" action="<?php echo e(route('stores.login'), false); ?>">
                                        <?php echo csrf_field(); ?>
                                        <div class="form-group row">
                                            

                                            <div class="col">
                                                <input id="email" type="text" placeholder="请输入登录账号" class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="phone" value="<?php echo e(old('email'), false); ?>" required autocomplete="email" autofocus>

                                                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>账号不存在</strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            

                                            <div class="col">
                                                <input id="password" type="password" placeholder="请输入登录密码" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="password" required autocomplete="current-password">

                                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>密码错误</strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>


                                        <div class="form-group row mb-0 mt-5">
                                            <div class="col">
                                                <button type="submit" class="btn btn-block btn-primary">
                                                    <?php echo e('登录', false); ?>

                                                </button>

                                                <?php if(Route::has('password.request')): ?>
                                                    <a class="btn btn-link" href="<?php echo e(route('password.request'), false); ?>">
                                                        <?php echo e(__('Forgot Your Password?'), false); ?>

                                                    </a>
                                                <?php endif; ?>
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
<?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/stores/login.blade.php ENDPATH**/ ?>
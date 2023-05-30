<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale()), false); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token(), false); ?>">

    <title>分销商后台管理<?php if(auth()->guard()->check()): ?> 【<?php echo e(Auth::user()->store_name, false); ?>】 <?php endif; ?></title>

    <!-- Scripts -->
    <script src="/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Fonts -->
    
    
    <script src="/vendor/retail/dist/js/bootstrap.bundle.js"></script>
    <script src="/vendor/retail/js/clipboard.min.js"></script>
    <!-- Styles -->
    <link href="<?php echo e(asset('vendor/retail/css/app.css'), false); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('vendor/retail/css/global.css'), false); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('vendor/laravel-admin/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css'), false); ?>" rel="stylesheet">


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
                <a class="navbar-brand" href="<?php echo e(url('/stores'), false); ?>">分销商后台管理</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="<?php echo e(__('Toggle navigation'), false); ?>">
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
                                ✉<?php echo e(\App\Models\Msg::getnum(), false); ?> <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <?php $__currentLoopData = \App\Models\Msg::getmsg(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="dropdown-item">
                                        <a href="/stores/msg?id=<?php echo e($item->id, false); ?>"><?php echo e($item->title, false); ?></a>



                                    </div>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </li>
                        <!-- Authentication Links -->
                        <?php if(auth()->guard()->guest()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('stores.login'), false); ?>"><?php echo e(__('Login'), false); ?></a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <?php echo e(Auth::user()->store_name, false); ?> <span class="caret"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="<?php echo e(route('stores.logout'), false); ?>"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">退出登录</a>

                                    <form id="logout-form" action="<?php echo e(route('stores.logout'), false); ?>" method="POST" style="display: none;">
                                        <?php echo csrf_field(); ?>
                                    </form>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-2">
                        <?php echo $__env->make('layouts.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                    <div class="col-md-10">
                        <div class="page-wrap" id="pjax-container">
                            <?php echo $__env->yieldContent('content'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <div class="position-fixed  right-0 p-3" style="z-index: 5; right: 0; top: 2.8rem;">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000">
              
              <div class="toast-body"></div>
            </div>
          </div>
    </div>


    <script src="/vendor/laravel-admin/jquery-pjax/jquery.pjax.js"></script>
    <script src="/vendor/laravel-admin/moment/min/moment-with-locales.min.js"></script>
    <script src="/vendor/laravel-admin/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
    <script src="/vendor/retail/js/retail.js"></script>
    <?php if(session('status')): ?>
        <script>
            $(function(){
                toast(<?php echo e(session('status'), false); ?>,"<?php echo e(session('message'), false); ?>")
            })
        </script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/layouts/app.blade.php ENDPATH**/ ?>
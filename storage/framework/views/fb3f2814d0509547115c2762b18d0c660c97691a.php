<?php $__env->startSection('content'); ?>

    <div class="box">
        <div class="box-title">
            <?php echo e($title, false); ?>

        </div>
        <div class="content">
            <?php echo $content; ?>

        </div>
    </div>
    <style>
        .box{
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .box-title{
            font-size: 1.5rem;
        }

    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/stores/msg.blade.php ENDPATH**/ ?>
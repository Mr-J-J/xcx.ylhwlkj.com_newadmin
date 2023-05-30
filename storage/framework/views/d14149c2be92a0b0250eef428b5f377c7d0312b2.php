
<?php $__currentLoopData = $default; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php echo $action->render(); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if(!empty($custom)): ?>
    <?php $__currentLoopData = $custom; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php echo $action->render(); ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php echo $__env->yieldContent('child'); ?>
<?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/admin/grid/actions/button.blade.php ENDPATH**/ ?>
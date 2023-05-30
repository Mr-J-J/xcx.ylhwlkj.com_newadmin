<div class="input-group input-group-sm">
    <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <?php echo $inline ? '<span class="icheck">' : '<div class="radio icheck">'; ?>


            <label <?php if($inline): ?>class="radio-inline"<?php endif; ?>>
                <input type="radio" class="<?php echo e($id, false); ?>" name="<?php echo e($name, false); ?>" value="<?php echo e($option, false); ?>" class="minimal" <?php echo e(((string)$option === request($name, is_null($value) ? '' : $value)) ? 'checked' : '', false); ?> />&nbsp;<?php echo e($label, false); ?>&nbsp;&nbsp;
            </label>

        <?php echo $inline ? '</span>' :  '</div>'; ?>


    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/admin/filter/radio.blade.php ENDPATH**/ ?>
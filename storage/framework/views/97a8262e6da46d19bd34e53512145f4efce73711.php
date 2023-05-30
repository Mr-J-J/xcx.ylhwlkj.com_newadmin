<div class="<?php echo e($viewClass['form-group'], false); ?> <?php echo !$errors->has($errorKey) ? '' : 'has-error'; ?>">

<label for="<?php echo e($id, false); ?>" class="<?php echo e($viewClass['label'], false); ?> control-label"><?php echo e($label, false); ?></label>

    <div class="<?php echo e($viewClass['field'], false); ?>">

        <?php echo $__env->make('admin::form.error', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <input type="hidden" name="<?php echo e($name, false); ?>"/>

        <select class="form-control <?php echo e($class, false); ?> hide" style="width: 100%;" name="<?php echo e($name, false); ?>" <?php echo $attributes; ?> >
            <option value=""></option>
            <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $select => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($select, false); ?>" <?php echo e($select == old($column, $value) ?'selected':'', false); ?>><?php echo e($option, false); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <div class="belongsto-<?php echo e($class, false); ?>">
            <?php echo $grid->render(); ?>

            <template class="empty">
                <?php echo $__env->make('admin::grid.empty-grid', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </template>
        </div>

        <?php echo $__env->make('admin::form.help-block', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    </div>
</div>
<?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/admin/form/belongsto.blade.php ENDPATH**/ ?>
<table class="table ">
    <thead>
    <tr>
                    <th>产品ID</th>
                    <th>名称</th>
                    <th>状态</th>
                    <th>操作</th>
            </tr>
    </thead>
    <tbody>
        <?php
            $script = '';
        ?>
        <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                if(!$k){
                    $script = $item[3]->addScript();
                }
            ?>
        <tr>
                <td><?php echo e($item[0], false); ?></td>
                <td><?php echo e($item[1], false); ?></td>
                <td><?php echo e($item[2], false); ?></td>
                <td><?php echo $item[3]->render(); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
</table>
<script>
    <?php echo $script; ?>

</script>


<?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/custom/pft/ticket_list.blade.php ENDPATH**/ ?>
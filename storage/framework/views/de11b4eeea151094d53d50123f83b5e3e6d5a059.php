<style>
    .item{
        margin-bottom: 20px;
        display:flex;
    }
    .item-label{
        margin-right:10px;
        color: #777
    }
    .item-body{
        color: #000
    }
</style>
<div class="box box-info">
    <div class="box-header with-border">订单信息</div>
    <div class="box-body">
        <?php $__currentLoopData = $detail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-4 item">
            <label class="item-label"><?php echo e($row['name'], false); ?></label>
            <div class="item-body"><?php echo e($row['value'], false); ?></div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php $__currentLoopData = $codeList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="box-header with-border">核销码 [<?php echo e($code->code, false); ?>]<strong>(<?php echo e($code->used_number, false); ?> / <?php echo e($code->check_number, false); ?>)</strong></div>
    <div class="box-body">
        <table class="table table-bordered" style="width:700px;">
            <?php if(!$code->check_logs->isEmpty()): ?>
            <tr>
                <th>核销时间</th>
                <th>核销单号</th>
                <th>核销账号</th>
                <th>核销数量</th>
                <th>核销金额</th>
            </tr>
              <?php $__currentLoopData = $code->check_logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $logs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($logs->created_at, false); ?></td>
                    <td><?php echo e($logs->check_sn, false); ?></td>
                    <td><?php echo e($logs->username, false); ?></td>
                    <td><?php echo e($logs->check_number, false); ?></td>
                    <td><?php echo e($logs->check_money, false); ?></td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            <?php else: ?>
                <tr>
                    <td colspan="4">暂无核销记录</td>
                </tr>
            <?php endif; ?>            
        </table>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    
   
    
</div>



<?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/custom/mall/show-order.blade.php ENDPATH**/ ?>
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
            <div class="item-body"><?php echo $row['value']; ?></div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>
    <div class="box-header with-border">订单竞价</div>
    <div class="box-body">
        <table class="table table-bordered" style="width:700px;">
            <tr>
                <th>时间</th>
                <th>商家</th>
                <th>出票率</th>
                <th>报价</th>
                <th>状态</th>
                <th>备注</th>
            </tr>
            <?php if($list): ?>
                <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row->created_at, false); ?></td>
                        <td><?php if($row->store): ?>[ID:<?php echo e($row->store->id, false); ?>]<?php echo e($row->store->store_name, false); ?><?php endif; ?></td>
                        <td><?php echo e($row->draw_rate, false); ?>%</td>
                        <td><?php echo e($row->offer_amount, false); ?></td>
                        <td><?php echo e($row->offer_status == 1 ?'中标':'-', false); ?></td>
                        <td><?php echo e($row->remark, false); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">暂无报价</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="box-header with-border">竞价流程</div>
    <div class="box-body">
        <table class="table table-bordered" style="width:700px;">
            <tr>
                <th>时间</th>
                <th>备注</th>
            </tr>
            <?php if($liucheng): ?>
                <?php $__currentLoopData = $liucheng; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row->created_at, false); ?></td>
                        <td><?php echo e($row->detail, false); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">暂无数据</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>



<?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/custom/offer/show-order.blade.php ENDPATH**/ ?>
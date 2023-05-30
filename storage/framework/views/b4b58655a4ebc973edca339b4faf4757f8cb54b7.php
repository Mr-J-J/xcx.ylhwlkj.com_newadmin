<?php $__env->startSection('content'); ?>
<div class="page-header">影旅卡订单</div>
<div class="row">
    <div class="col">
        <form action="" method="get" pjax-container class="row  justify-content-end">
            <div class="col-md-2 col-xs-6 col-lg-2">
                <select class="form-control" name="card_id">
                    <option value="">影旅卡类型</option>
                    <?php $__currentLoopData = $cardList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key, false); ?>" <?php if(request('card_id',0) == $key): ?> selected <?php endif; ?>><?php echo e($card, false); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3 col-xs-6">
                <input type="text" class="form-control" name="keywords" placeholder="输入订单号搜索" value="<?php echo e(request('keywords',''), false); ?>">
            </div>
            <div class="col-md-3 col-xs-4 col-lg-2">
                <button type="submit" class="btn btn-primary search-btn">搜索</button>
                <a href="/<?php echo e(request()->path(), false); ?>" class="btn btn-light reset-btn">重置</a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="table-list table-responsive m-4">
        <table class="table">
            <thead>
                <tr>
                  <th scope="col">订单号</th>
                  <th scope="col">订单状态</th>
                  <th scope="col">影旅卡类型</th>
                  <th scope="col">订单类型</th>
                  <th scope="col">实付款</th>
                  <th scope="col">会员手机号</th>
                  <th scope="col">下单时间</th>
                  
                </tr>
              </thead>
              <tbody>
                  <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <?php echo e($order->order_sn, false); ?>

                        </td>
                        <td><?php echo e(\App\CardModels\CardOrder::$status[$order->order_status], false); ?></td>
                        <td>
                            <?php if(!empty($cardList[$order->card_id])): ?>
                                <?php echo e($cardList[$order->card_id], false); ?>

                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo e($order->remark, false); ?>

                        </td>
                        <td>
                            <?php echo e($order->order_amount, false); ?>

                        </td>
                        <td>
                            <?php echo e(str_replace(substr($order->mobile,3,4),'****',$order->mobile), false); ?>

                        </td>
                        <td>
                            <?php echo e($order->created_at, false); ?>

                        </td>
                    </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

              </tbody>
          </table>
    </div>
</div>
<div class="row justify-content-center">
    <?php echo e($list->links(), false); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/stores/order.blade.php ENDPATH**/ ?>
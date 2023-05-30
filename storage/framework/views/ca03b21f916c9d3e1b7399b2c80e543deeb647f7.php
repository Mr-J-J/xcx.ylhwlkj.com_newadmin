<?php $__env->startSection('content'); ?>
<style>
    .account-info{font-size:1rem;align-items: center}
    .balance{font-size:2rem;}
    .money{color:#FD6B31;font-weight:600}
</style>
<div class="page-header">账户累计收入</div>
<div class="row">
    <div class="col row justify-content-between account-info">
        <div class="col text-muted"><span class="balance mr-1 money"><?php echo e($storeInfo->total_money, false); ?></span>元</div>
        <div class="col text-right text-muted">
             <span class="label">待结算：</span><span class="money"><?php echo e($storeInfo->balance, false); ?></span>元
             <span class="label ml-4">已结算：</span><span class="money"><?php echo e($storeInfo->settle_money, false); ?></span>元
        </div>
    </div>
</div>
<div class="row mt-2">
    <div class="col">
        <ul class="page-tab nav nav-pills mb-3" id="pills-tab" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" href="/stores/settle" >结款明细</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" href="/stores/withdraw" style="font-weight: bold">佣金提现</a>
              </li>
              <li class="nav-item" role="presentation">
                  <a class="nav-link" href="/stores/withdrawList">提现记录</a>
              </li>
        </ul>
    </div>
</div>
<div class="row">
    <div class="col">
        <form action="/stores/settle" method="get" pjax-container class="row form-inline justify-content-end">
            <div class="form-group mr-3">
                <input type="text" class="form-control"  autocomplete="off" style="width: 130px" value="<?php echo e(request('created_at.start',''), false); ?>" id="created_at_start" name="created_at[start]" placeholder="开始日期">
                <div style="padding: 0 10px"> - </div>
                <input type="text" class="form-control" autocomplete="off"   style="width: 130px" value="<?php echo e(request('created_at.end',''), false); ?>" id="created_at_end" name="created_at[end]" placeholder="结束日期">
            </div>
            <div class="form-group mr-3">
                <?php
                    $keywords = filter_var(request('keywords',''), FILTER_SANITIZE_STRING);

                ?>
                <input type="text" class="form-control" name="keywords" value="<?php echo e($keywords, false); ?>" placeholder="输入结款单号搜索" id="">
            </div>
            <div class="form-group mr-4">
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
                  <th scope="col">结款单号</th>
                  <th scope="col">结款金额</th>
                  <th scope="col">余额</th>
                  <th scope="col">结款时间</th>
                  <th scope="col">结款凭证</th>
                </tr>
              </thead>
              <tbody>
                  <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $settle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <?php echo e($settle->settle_sn, false); ?>

                        </td>
                        <td><?php echo e($settle->settle_money, false); ?></td>
                        <td><?php echo e($settle->after_balance, false); ?></td>
                        <td>
                            <?php echo e($settle->created_at, false); ?>

                        </td>
                        <td>
                           <?php if($settle->images): ?>
                            <a href="<?php echo e($settle->images, false); ?>"><img src="<?php echo e($settle->images, false); ?>" width="50" height="50" alt=""></a>
                           <?php endif; ?>
                        </td>

                    </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

              </tbody>
          </table>
    </div>
</div>
<script>
    $(function(){
        $('#created_at_start').datetimepicker({"format":"YYYY-MM-DD","locale":"zh-CN"});
        $('#created_at_end').datetimepicker({"format":"YYYY-MM-DD","locale":"zh-CN","useCurrent":false});
        $("#created_at_start").on("dp.change", function (e) {
            $('#created_at_end').data("DateTimePicker").minDate(e.date);
        });
        $("#created_at_end").on("dp.change", function (e) {
            $('#created_at_start').data("DateTimePicker").maxDate(e.date);
        });
    })

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/stores/settle.blade.php ENDPATH**/ ?>
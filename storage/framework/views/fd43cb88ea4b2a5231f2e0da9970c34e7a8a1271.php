<?php $__env->startSection('content'); ?>

    <div class="row">
        <div class="col">
            <form action="" method="get" pjax-container class="row  justify-content-end">
                <div class="form-group mr-3">
                    <select name="type"  class="form-control">
                        <option value="">订单分类</option>
                        <option value="1">肯德基订单</option>
                        <option value="2">麦当劳订单</option>
                        <option value="2">奈雪订单</option>
                        <option value="2">星巴克订单</option>
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
                    <th scope="col">商品名称</th>
                    <th scope="col">订单价格</th>
                    <th scope="col">数量</th>
                    <th scope="col">手机号</th>
                    <th scope="col">下单时间</th>
                    
                </tr>
                </thead>
                <tbody>
























                </tbody>

            </table>
            <div style="text-align: center">待开通</div>
        </div>
    </div>
    <div class="row justify-content-center">

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/stores/xbk.blade.php ENDPATH**/ ?>
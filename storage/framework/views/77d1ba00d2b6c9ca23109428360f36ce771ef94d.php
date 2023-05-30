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
<div style="width: 100%;display: flex;justify-content: center">
    <div id="left" style="width: 600px;height:400px;"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.1/dist/echarts.min.js"></script>
<script type="text/javascript">
    var myChart1 = echarts.init(document.getElementById('left'));

    // 指定图表的配置项和数据
    var option = {
        title: {
            text: '佣金'
        },
        tooltip: {},
        legend: {
            data: ['订单']
        },
        xAxis: {
            axisLabel: {
                show: false // 隐藏x轴标签
            },
            data: <?php echo json_encode($movie, 15, 512) ?>
        },
        yAxis: {
            axisLabel: {
                formatter: '{value} 元' // 给y轴标签加上单位元
            }
        },
        series: [
            {
                name: '佣金',
                type: 'bar',
                data: <?php echo json_encode($money, 15, 512) ?>,
                itemStyle: {
                    color: function(params) { // 根据参数返回不同的颜色
                        var r = Math.floor(Math.random() * 256); // 随机生成0-255之间的整数
                        var g = Math.floor(Math.random() * 256);
                        var b = Math.floor(Math.random() * 256);
                        return 'rgb(' + r + ',' + g + ',' + b + ')'; // 调用随机颜色函数
                    }
                }
            },
            {
                name: '佣金',
                type: 'line',
                data: <?php echo json_encode($money, 15, 512) ?>,
                smooth: true
            }
        ]
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart1.setOption(option);
</script>
<div class="row mt-2">
    <div class="col">
        <ul class="page-tab nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
              <a class="nav-link active" href="/stores/account"  >分成明细</a>
            </li>
        </ul>
    </div>
</div>
<div class="row">
    <div class="col">
        <form action="/stores/account" method="get" pjax-container class="row form-inline justify-content-end">
            <div class="form-group mr-3">
                <?php
                    $type = (int)request('type',0);
                ?>
                <select name="type"  class="form-control">
                    <option value="">按类别筛选</option>
                    <option value="1" <?php if($type == 1): ?> selected <?php endif; ?>>影旅卡订单分成</option>
                    <option value="2"  <?php if($type == 2): ?> selected <?php endif; ?>>电影票订单分成</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <input type="text" class="form-control" value="<?php echo e(request('keywords',''), false); ?>" name="keywords" placeholder="输入订单号搜索">
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
                  <th scope="col">描述</th>
                  <th scope="col">订单号</th>
                  <th scope="col">返佣金额/余额</th>
                    <th scope="col">佣金比例</th>
                  <th scope="col">结算状态</th>
                  <th scope="col">创建时间</th>

                  <th scope="col">电影名称</th>
                  <th scope="col">购票手机号</th>
                  <th scope="col">影院详情</th>
                    <th scope="col">原价</th>
                    <th scope="col">优惠</th>
                  <th scope="col">付款</th>
                </tr>
              </thead>
              <tbody>
                  <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <?php echo e($detail->remark, false); ?>

                        </td>
                        <td>
                            <?php echo e($detail->order_sn, false); ?>

                        </td>
                        <td><?php echo e($detail->money, false); ?> / <?php echo e($detail->after_balance, false); ?></td>
                        <td>
                            <?php if(!empty($detail->bili)): ?>
                                <?php echo e($detail->bili, false); ?>%
                            <?php endif; ?>

                            <?php if(empty($detail->bili)): ?>
                                无信息
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($detail->state): ?>
                                <span class="badge badge-success">已结算</span>
                            <?php else: ?>
                                <span class="badge badge-light">待结算</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo e($detail->created_at, false); ?>

                        </td>





                        <td>
                            <?php echo e($detail->info['movie_name'], false); ?>

                            <?php if(empty($detail->info['movie_name'])): ?>
                                无信息
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo e($detail->info['buyer_phone'], false); ?>

                            <?php if(empty($detail->info['movie_name'])): ?>
                                无信息
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($detail->info['movie_name'])): ?>
                                <?php echo e($detail->info['cinemas'], false); ?><?php echo e($detail->info['halls'], false); ?><?php echo e($detail->info['seat_names'], false); ?>

                            <?php endif; ?>

                            <?php if(empty($detail->info['movie_name'])): ?>
                                无信息
                            <?php endif; ?>
                        </td>
                        <td>

                            <?php if(!empty($detail->info['movie_name'])): ?>
                                <?php echo e($detail->info['market_price'], false); ?>

                            <?php endif; ?>
                            <?php if(empty($detail->info['movie_name'])): ?>
                                无信息
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($detail->info['movie_name'])): ?>
                                <?php echo e($detail->info['discount_price'], false); ?>

                            <?php endif; ?>

                            <?php if(empty($detail->info['movie_name'])): ?>
                                无信息
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($detail->info['movie_name'])): ?>
                                <?php echo e($detail->info['amount'], false); ?>

                            <?php endif; ?>

                            <?php if(empty($detail->info['movie_name'])): ?>
                                无信息
                            <?php endif; ?>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/stores/account.blade.php ENDPATH**/ ?>
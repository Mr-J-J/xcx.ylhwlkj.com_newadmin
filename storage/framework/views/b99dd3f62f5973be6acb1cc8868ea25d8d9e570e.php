<?php $__env->startSection('content'); ?>
    <div class="page-header">电影票订单</div>
    <div style="width: 100%;display: flex;justify-content: center">
        <div id="left" style="width: 600px;height:400px;"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.1/dist/echarts.min.js"></script>
    <script type="text/javascript">
        var myChart1 = echarts.init(document.getElementById('left'));

        // 指定图表的配置项和数据
        var option = {
            title: {
                text: '订单'
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
                    name: '订单',
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
                    name: '订单',
                    type: 'line',
                    data: <?php echo json_encode($money, 15, 512) ?>,
                    smooth: true
                }
            ]
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart1.setOption(option);
    </script>

    <div class="row">
        <div class="col">
            <form action="" method="get" pjax-container class="row  justify-content-end">
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
                    <th scope="col">电影名称</th>
                    <th scope="col">订单金额</th>
                    <th scope="col">购票数量</th>
                    <th scope="col">购票手机号</th>
                    <th scope="col">下单时间</th>
                    
                </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <?php echo e($order->order_no, false); ?>

                        </td>
                        <td><?php echo e($order->status_txt, false); ?></td>
                        <td>
                            <?php echo e($order->movie_name, false); ?>

                        </td>
                        <td>
                            <?php echo e($order->amount, false); ?>

                        </td>
                        <td>
                            <?php echo e($order->ticket_count, false); ?>

                        </td>
                        <td>
                            <?php echo e(str_replace(substr($order->buyer_phone,3,4),'****',$order->buyer_phone), false); ?>

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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/stores/orderpiao.blade.php ENDPATH**/ ?>
@extends('layouts.app')

@section('content')
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
                data: @json($movie)
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
                    data: @json($money),
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
                    data: @json($money),
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
                    <input type="text" class="form-control" name="keywords" placeholder="输入订单号搜索" value="{{request('keywords','')}}">
                </div>
                <div class="col-md-3 col-xs-4 col-lg-2">
                    <button type="submit" class="btn btn-primary search-btn">搜索</button>
                    <a href="/{{request()->path()}}" class="btn btn-light reset-btn">重置</a>
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
                    {{-- <th>操作</th> --}}
                </tr>
                </thead>
                <tbody>
                @foreach($list as $order)
                    <tr>
                        <td>
                            {{$order->order_no}}
                        </td>
                        <td>{{$order->status_txt}}</td>
                        <td>
                            {{$order->movie_name}}
                        </td>
                        <td>
                            {{$order->amount}}
                        </td>
                        <td>
                            {{$order->ticket_count}}
                        </td>
                        <td>
                            {{str_replace(substr($order->buyer_phone,3,4),'****',$order->buyer_phone)}}
                        </td>
                        <td>
                            {{$order->created_at}}
                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>
    <div class="row justify-content-center">
        {{ $list->links() }}
    </div>
@endsection

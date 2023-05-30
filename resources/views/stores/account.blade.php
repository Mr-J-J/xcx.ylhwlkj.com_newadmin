@extends('layouts.app')

@section('content')
<style>
    .account-info{font-size:1rem;align-items: center}
    .balance{font-size:2rem;}
    .money{color:#FD6B31;font-weight:600}
</style>
<div class="page-header">账户累计收入</div>
<div class="row">
   <div class="col row justify-content-between account-info">
       <div class="col text-muted"><span class="balance mr-1 money">{{$storeInfo->total_money}}</span>元</div>
       <div class="col text-right text-muted">
            <span class="label">待结算：</span><span class="money">{{$storeInfo->balance}}</span>元
            <span class="label ml-4">已结算：</span><span class="money">{{$storeInfo->settle_money}}</span>元
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
            data: @json($movie)
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
                name: '佣金',
                type: 'line',
                data: @json($money),
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
                @php
                    $type = (int)request('type',0);
                @endphp
                <select name="type"  class="form-control">
                    <option value="">按类别筛选</option>
                    <option value="1" @if($type == 1) selected @endif>影旅卡订单分成</option>
                    <option value="2"  @if($type == 2) selected @endif>电影票订单分成</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <input type="text" class="form-control" value="{{request('keywords','')}}" name="keywords" placeholder="输入订单号搜索">
            </div>
            <div class="form-group mr-4">
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
                  <th scope="col">描述</th>
                  <th scope="col">订单号</th>
                  <th scope="col">返佣金额/余额</th>
                    <th scope="col">佣金比例</th>
                  <th scope="col">结算状态</th>
                  <th scope="col">创建时间</th>
{{--                  <th scope="col">卡类型</th>--}}
                  <th scope="col">电影名称</th>
                  <th scope="col">购票手机号</th>
                  <th scope="col">影院详情</th>
                    <th scope="col">原价</th>
                    <th scope="col">优惠</th>
                  <th scope="col">付款</th>
                </tr>
              </thead>
              <tbody>
                  @foreach($list as $detail)
                    <tr>
                        <td>
                            {{$detail->remark}}
                        </td>
                        <td>
                            {{$detail->order_sn}}
                        </td>
                        <td>{{$detail->money}} / {{$detail->after_balance}}</td>
                        <td>
                            @if(!empty($detail->bili))
                                {{$detail->bili}}%
                            @endif

                            @if(empty($detail->bili))
                                无信息
                            @endif
                        </td>
                        <td>
                            @if($detail->state)
                                <span class="badge badge-success">已结算</span>
                            @else
                                <span class="badge badge-light">待结算</span>
                            @endif
                        </td>
                        <td>
                            {{$detail->created_at}}
                        </td>
{{--                        <td>--}}
{{--                            @if(!empty($cardList[$detail->card_id]))--}}
{{--                                {{$cardList[$detail->card_id]}}--}}
{{--                            @endif--}}
{{--                        </td>--}}
                        <td>
                            {{$detail->info['movie_name']}}
                            @if(empty($detail->info['movie_name']))
                                无信息
                            @endif
                        </td>
                        <td>
                            {{$detail->info['buyer_phone']}}
                            @if(empty($detail->info['movie_name']))
                                无信息
                            @endif
                        </td>
                        <td>
                            @if(!empty($detail->info['movie_name']))
                                {{$detail->info['cinemas']}}{{$detail->info['halls']}}{{$detail->info['seat_names']}}
                            @endif

                            @if(empty($detail->info['movie_name']))
                                无信息
                            @endif
                        </td>
                        <td>

                            @if(!empty($detail->info['movie_name']))
                                {{$detail->info['market_price']}}
                            @endif
                            @if(empty($detail->info['movie_name']))
                                无信息
                            @endif
                        </td>
                        <td>
                            @if(!empty($detail->info['movie_name']))
                                {{$detail->info['discount_price']}}
                            @endif

                            @if(empty($detail->info['movie_name']))
                                无信息
                            @endif
                        </td>
                        <td>
                            @if(!empty($detail->info['movie_name']))
                                {{$detail->info['amount']}}
                            @endif

                            @if(empty($detail->info['movie_name']))
                                无信息
                            @endif
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

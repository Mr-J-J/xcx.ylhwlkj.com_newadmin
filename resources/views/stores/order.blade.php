@extends('layouts.app')

@section('content')
<div class="page-header">影旅卡订单</div>
<div class="row">
    <div class="col">
        <form action="" method="get" pjax-container class="row  justify-content-end">
            <div class="col-md-2 col-xs-6 col-lg-2">
                <select class="form-control" name="card_id">
                    <option value="">影旅卡类型</option>
                    @foreach($cardList as $key=>$card)
                        <option value="{{$key}}" @if(request('card_id',0) == $key) selected @endif>{{$card}}</option>
                    @endforeach
                </select>
            </div>
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
                  <th scope="col">影旅卡类型</th>
                  <th scope="col">订单类型</th>
                  <th scope="col">实付款</th>
                  <th scope="col">会员手机号</th>
                  <th scope="col">下单时间</th>
                  {{-- <th>操作</th> --}}
                </tr>
              </thead>
              <tbody>
                  @foreach($list as $order)
                    <tr>
                        <td>
                            {{$order->order_sn}}
                        </td>
                        <td>{{\App\CardModels\CardOrder::$status[$order->order_status]}}</td>
                        <td>
                            @if(!empty($cardList[$order->card_id]))
                                {{$cardList[$order->card_id]}}
                            @endif
                        </td>
                        <td>
                            {{$order->remark}}
                        </td>
                        <td>
                            {{$order->order_amount}}
                        </td>
                        <td>
                            {{str_replace(substr($order->mobile,3,4),'****',$order->mobile)}}
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

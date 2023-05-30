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
                <input type="text" class="form-control"  autocomplete="off" style="width: 130px" value="{{request('created_at.start','')}}" id="created_at_start" name="created_at[start]" placeholder="开始日期">
                <div style="padding: 0 10px"> - </div>
                <input type="text" class="form-control" autocomplete="off"   style="width: 130px" value="{{request('created_at.end','')}}" id="created_at_end" name="created_at[end]" placeholder="结束日期">
            </div>
            <div class="form-group mr-3">
                @php
                    $keywords = filter_var(request('keywords',''), FILTER_SANITIZE_STRING);

                @endphp
                <input type="text" class="form-control" name="keywords" value="{{$keywords}}" placeholder="输入结款单号搜索" id="">
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
                  <th scope="col">结款单号</th>
                  <th scope="col">结款金额</th>
                  <th scope="col">余额</th>
                  <th scope="col">结款时间</th>
                  <th scope="col">结款凭证</th>
                </tr>
              </thead>
              <tbody>
                  @foreach($list as $settle)
                    <tr>
                        <td>
                            {{$settle->settle_sn}}
                        </td>
                        <td>{{$settle->settle_money}}</td>
                        <td>{{$settle->after_balance}}</td>
                        <td>
                            {{$settle->created_at}}
                        </td>
                        <td>
                           @if($settle->images)
                            <a href="{{$settle->images}}"><img src="{{$settle->images}}" width="50" height="50" alt=""></a>
                           @endif
                        </td>

                    </tr>
                  @endforeach

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
@endsection

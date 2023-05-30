@extends('layouts.app')

@section('content')
<style>
    .account-info{font-size:1rem;align-items: center}
    .balance{font-size:2rem;}
    .money{color:#FD6B31;font-weight:600}
</style>
<div class="page-header">提现记录</div>
<div class="row mt-2">
    <div class="col">
        <ul class="page-tab nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="/stores/settle" >结款明细</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link " href="/stores/withdraw" >佣金提现</a>
              </li>
              <li class="nav-item" role="presentation">
                  <a class="nav-link active" href="/stores/withdrawList">提现记录</a>
              </li>
        </ul>
    </div>
</div>
<div class="row">
    <div class="col">
        <form action="/stores/withdrawList" method="get" pjax-container class="form-inline row  justify-content-end">
            <div class="form-group mr-3">
                <input type="text" class="form-control"  autocomplete="off" style="width: 130px" value="{{request('created_at.start','')}}" id="created_at_start" name="created_at[start]" placeholder="开始日期">
                <div style="padding: 0 10px"> - </div>
                <input type="text" class="form-control" autocomplete="off"   style="width: 130px" value="{{request('created_at.end','')}}" id="created_at_end" name="created_at[end]" placeholder="结束日期">
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
                  <th scope="col">提现日期</th>
                  <th scope="col">提现金额</th>
                  <th scope="col">提现账号</th>
                  <th scope="col">提现状态</th>
                </tr>
              </thead>
              <tbody>
                  @foreach($list as $detail)
                    <tr>
                        <td>
                            {{$detail->created_at}}
                        </td>
                        <td>
                            -{{$detail->money}}
                        </td>
                        <td>{{$detail->draw_account}}( {{$detail->account_name}} )</td>
                        <td>
                            @if($detail->state == 1)
                                <span class="badge badge-success">提现成功</span>
                            @elseif($detail->state == 2)
                                <span class="badge badge-danger">提现失败</span>
                            @else
                                <span class="badge badge-light">审核中</span>
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

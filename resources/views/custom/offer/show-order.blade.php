<style>
    .item{
        margin-bottom: 20px;
        display:flex;
    }
    .item-label{
        margin-right:10px;
        color: #777
    }
    .item-body{
        color: #000
    }
</style>
<div class="box box-info">
    <div class="box-header with-border">订单信息</div>
    <div class="box-body">
        @foreach($detail as $row)
        <div class="col-md-4 item">
            <label class="item-label">{{$row['name']}}</label>
            <div class="item-body">{!! $row['value'] !!}</div>
        </div>
        @endforeach

    </div>
    <div class="box-header with-border">订单竞价</div>
    <div class="box-body">
        <table class="table table-bordered" style="width:700px;">
            <tr>
                <th>时间</th>
                <th>商家</th>
                <th>出票率</th>
                <th>报价</th>
                <th>状态</th>
                <th>备注</th>
            </tr>
            @if($list)
                @foreach($list as $row)
                    <tr>
                        <td>{{$row->created_at}}</td>
                        <td>@if($row->store)[ID:{{$row->store->id}}]{{$row->store->store_name}}@endif</td>
                        <td>{{$row->draw_rate}}%</td>
                        <td>{{$row->offer_amount}}</td>
                        <td>{{$row->offer_status == 1 ?'中标':'-'}}</td>
                        <td>{{$row->remark}}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">暂无报价</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="box-header with-border">竞价流程</div>
    <div class="box-body">
        <table class="table table-bordered" style="width:700px;">
            <tr>
                <th>时间</th>
                <th>备注</th>
            </tr>
            @if($liucheng)
                @foreach($liucheng as $row)
                    <tr>
                        <td>{{$row->created_at}}</td>
                        <td>{{$row->detail}}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">暂无数据</td>
                </tr>
            @endif
        </table>
    </div>
</div>




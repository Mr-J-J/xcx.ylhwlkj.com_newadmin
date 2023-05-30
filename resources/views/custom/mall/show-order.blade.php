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
            <div class="item-body">{{$row['value']}}</div>
        </div>
        @endforeach
    </div>

    @foreach($codeList as $code)
    <div class="box-header with-border">核销码 [{{$code->code}}]<strong>({{$code->used_number}} / {{$code->check_number}})</strong></div>
    <div class="box-body">
        <table class="table table-bordered" style="width:700px;">
            @if(!$code->check_logs->isEmpty())
            <tr>
                <th>核销时间</th>
                <th>核销单号</th>
                <th>核销账号</th>
                <th>核销数量</th>
                <th>核销金额</th>
            </tr>
              @foreach($code->check_logs as $logs)
                <tr>
                    <td>{{$logs->created_at}}</td>
                    <td>{{$logs->check_sn}}</td>
                    <td>{{$logs->username}}</td>
                    <td>{{$logs->check_number}}</td>
                    <td>{{$logs->check_money}}</td>
                </tr>
              @endforeach
            
            @else
                <tr>
                    <td colspan="4">暂无核销记录</td>
                </tr>
            @endif            
        </table>
    </div>
    @endforeach
    
   
    
</div>




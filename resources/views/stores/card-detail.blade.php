@extends('layouts.app')

@section('content')
<div class="page-header">会员消费明细</div>
<div class="row">
    <div class="col">
        <span class="text-muted">昵称：</span><span>{{$nickname}}</span>  <span class="text-muted ml-3">手机号：</span><span>{{$mobile}}</span>
    </div>    
</div>
<div class="row">
    <div class="table-list table-responsive m-4">
        <table class="table">
            <thead>
                <tr>
                  <th scope="col">订单号</th>
                  <th scope="col">下单时间</th>
                  <th scope="col">影旅卡类型</th>
                  <th scope="col">消费金额</th>
                </tr>
              </thead>
              <tbody>
                  @foreach($list as $detail)
                    <tr>
                        <td>
                            {{$detail->order_no}}
                        </td>
                        <td>{{$detail->created_at}}</td>
                        <td>
                            @if(!empty($cardList[$detail->card_id]))
                                {{$cardList[$detail->card_id]}}
                            @endif
                        </td>
                        <td>
                            {{$detail->money}}
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

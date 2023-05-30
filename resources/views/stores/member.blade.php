@extends('layouts.app')

@section('content')
<div class="page-header">用户管理</div>
<div class="row">
    <div class="col">
        <form action="/stores/member" method="get" pjax-container class="row  justify-content-end">
            <div class="col-md-3 col-xs-6">
                <input type="text" class="form-control" name="keywords" placeholder="输入手机号搜索" id="">
            </div>
            <div class="col-md-3 col-xs-4">
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
                  <th scope="col">昵称</th>
                  <th scope="col">手机号</th>
                  <th scope="col">消费金额</th>
                  <th scope="col">注册时间</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                  @foreach($list as $member)
                    <tr>
                        <td>
                            {{$member->nickname}}
                        </td>
                        <td>{{str_replace(substr($member->mobile,3,4),'****',$member->mobile)}}</td>
                        <td>
                            {{$member->cash_money}}
                        </td>
                        <td>
                            {{$member->created_at}}
                        </td>
                        <td>
                            <a href="/stores/card-detail/{{$member->id}}" class="btn btn-sm btn-primary">查看消费明细</a>
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

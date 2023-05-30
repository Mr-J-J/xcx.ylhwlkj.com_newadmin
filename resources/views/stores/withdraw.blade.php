@extends('layouts.app')

@section('content')
<style>
    .col-form-label{text-align: right}
    .store_logo{
        width: 75px;
        height: 75px;
        border-radius: 100%;
        object-fit:contain;
    }
    .item{
        display: flex;

    }
    .zhifubaofont{
        font-size: 1.3rem;
        font-weight: bold;
    }
    .box1{
        background: whitesmoke;
        padding: 1rem;
        border-radius: 15px;
        margin-top: 1rem;
    }
    .col-sm-3{
        padding-left: 0;
    }
    .tixian{
        font-size: 1.2rem;
        padding: 0.5rem;
        font-weight: bold;
    }
    .tixianinput{
        margin-top: 1rem;
        background: #f5f5f500;
        border: 0;
        font-size: 1rem;
    }
    .form-control{
        margin-top: 0.3rem;
    }
    .tixianright{
        display: flex;
        flex-wrap: nowrap;
        align-content: center;
        justify-content: center;
        align-items: center;
        border-bottom: 1px solid #0000002e;
    }
    .ph{
        color: red;
        font-weight: bold;
    }
    .ph1{
        color: #00000059;
        font-weight: bold;
        font-size: 0.2rem;
    }
    .button{
        display: flex;
        width: 100%;
        justify-content: center;
    }
</style>

<div class="page-header">账户余额提现</div>
<div class="row justify-content-center">
    <div class="col-md-12">
        <div>
            <ul class="page-tab nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="/stores/settle" >结款明细</a>
                  </li>
                  <li class="nav-item" role="presentation">
                    <a class="nav-link active" href="/stores/withdraw" >佣金提现</a>
                  </li>
                  <li class="nav-item" role="presentation">
                      <a class="nav-link" href="/stores/withdrawList">提现记录</a>
                  </li>
              </ul>
              <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                    <form action="/stores/dowithdraw" method="post">
                        @csrf
                        <div class="col-md-6">
                            <div class="form-group item box1">
                                <img class="store_logo" src="/upload/images/news1.png" alt="">
                                <div class="col-sm-8">
                                    <div class="form-control-plaintext zhifubaofont">支付宝</div>
                                    <div class="form-control-plaintext">{{$alipay_name}}</div>
                                </div>
                            </div>
                            <div class="box1">
                                <div class="form-group">
                                    <text class="col-sm-3 tixian">提现金额(元)</text>
                                    <div class="col-sm-8 tixianright">
                                        ¥
                                        {{--                                    <input type="number"  name="money"  class="form-control tixianinput" placeholder="请输入提现金额" value="{{$balance}}">--}}
                                        <input type="number"  name="money"  class="form-control tixianinput" placeholder="请输入提现金额">
                                    </div>
                                    <div class="ph">可提现:¥{{$balance}}</div>
                                </div>

                                <div class="form-group" style="margin-top: 30px">
                                    <div class="ph1">每日可提现1次</div>
                                    <div class="ph1">体现暂不收取手续费</div>
                                    <div class="ph1">单笔体现金额上限为5000元</div>
                                </div>
                            </div>

                            <div class="box1">
                            <div class="form-group item">
                                <text class="col-sm-3 form-control-plaintext">分销商：</text>
                                <div class="col-sm-8">
                                    <div class="form-control-plaintext">{{$store_name}}({{$id}})</div>
                                </div>
                            </div>
                            <div class="form-group item">
                                <text  class="col-sm-3 form-control-plaintext">可提现金额</text>
                                <div class="col-sm-8">
                                    <div class="form-control-plaintext">{{$balance}}</div>
                                </div>
                            </div>
                            <div class="form-group">
                                <text  class="col-sm-3">支付宝姓名：</text>
                                <div class="col-sm-8">
                                    <input type="text" name="alipay_name" class="form-control"  placeholder="请输入支付宝姓名" value="{{$alipay_name}}">
                                </div>
                            </div>
                            <div class="form-group">
                                <text  class="col-sm-3">支付宝账号：</text>
                                <div class="col-sm-8">
                                    <input type="text"  name="alipay_account"  class="form-control" placeholder="请输入支付宝账号" value="{{$alipay_account}}">
                                </div>
                            </div>
                            </div>
                            <div class="form-group" style="margin-top: 30px;">
                                <div class="col-sm-8 button">
                                    <button type="submit" class="btn btn-md btn-primary">确认提现</button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
              </div>
        </div>
    </div>
</div>
@endsection

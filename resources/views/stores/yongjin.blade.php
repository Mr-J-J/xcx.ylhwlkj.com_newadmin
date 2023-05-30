
@extends('layouts.app')
@section('content')
<div id="app3">
    <el-tabs tab-position='top' type="border-card">
        <el-tab-pane label="分销商信息">

            <div class="page-header">影旅卡列表</div>
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div>
                        <ul class="page-tab nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">基本信息</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label  class="col-sm-3 col-form-label">分销商ID：</label>
                                        <div class="col-sm-8">
                                            <input type="text" readonly class="form-control-plaintext" value="{{$store->id}}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label  class="col-sm-3 col-form-label">分销商名称：</label>
                                        <div class="col-sm-8">
                                            <input type="text"  readonly class="form-control-plaintext" value="{{$store->store_name}}">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label  class="col-sm-3 col-form-label">分销商类型：</label>
                                        <div class="col-sm-8">
                                            <input type="text" readonly class="form-control-plaintext" value="{{$store->type}}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label  class="col-sm-3 col-form-label">手机号码：</label>
                                        <div class="col-sm-8">
                                            <input type="text" readonly class="form-control-plaintext" value="{{$store->phone}}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label  class="col-sm-3 col-form-label">品牌Logo：</label>
                                        <div class="col-sm-8">
                                            <div class="form-control-plaintext"><img src="{{$store->store_logo}}" class="store_logo " alt=""></div>

                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label  class="col-sm-2 col-form-label"></label>
                                        <div class="col-sm-8">
                                            {{--  <button class="btn btn-md btn-primary">修改资料</button>  --}}
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </el-tab-pane>
        <el-tab-pane label="小程序分销">

            <div>
                <div class="page-header">小程序推广</div>
                <div class="page-header">公众号推广小程序  <span>示例</span></div>
                <div>
                    <p>第一步：公众号关联小程序</p>
                    <div class="tips-item">①进入公众号 → 广告与服务 → 小程序管理 → 关联小程序 (小程序APPID:<span class="copy-text">{{config('wechat.mini_program.default1.app_id')}}</span>)  <button type="button"  class="copy-btn btn btn-sm btn-outline-info">复制</button></div>
                    <p>第二步: 配置公众号自定义菜单</p>
                    <div class="tips-item">①配置菜单内容为“跳转到小程序”。</div>
                    <div class="tips-item">②跳转到小程序首页路径：<span class="copy-text">pages/index/index?com_id={{$store->id}}</span>  <button type="button"   class="copy-btn btn btn-sm btn-outline-info">复制</button></div>
                </div>
            </div>
        </el-tab-pane>
        <el-tab-pane label="海报分享">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">海报-推广</h3>
                </div>
                <div id="app4">
                    <el-tooltip class="item" effect="dark" content="参与出票获取更多佣金" placement="left-end">
                        <el-link :underline="false" @click="gopiao">申请票商</el-link>
                    </el-tooltip>

                    <el-carousel height="35rem">
                        @foreach($list as $item)
                            <el-carousel-item height="30rem">
                                <div style="display: flex;justify-content: center;align-items: center;flex-direction: column">
                                    <div class="box-body" style="height: 25rem;width: 18rem;">
                                        <el-image style="height: 100%;width: 100%;" src="{{$item->lphoto}}" alt="" class="img-responsive">
                                    </div>
                                    <div class="box-footer">
                                        <div @click="getimg({{$item}})">
                                            <a href="#myPopup" class="btn btn-block btn-danger font1" style="margin-top: 0.5rem" data-rel="popup" data-position-to="window">
                                                {{--                                    <a href="#myPopup" data-rel="popup" class="ui-btn" data-transition="fade">生成</a>--}}
                                                生成
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </el-carousel-item>
                        @endforeach
                    </el-carousel>
                    <el-dialog
                        top="10vh"
                        title="个人海报"
                        :visible.sync="visible"
                        width="80%"
                    >
                        <span style="width: 100%;display: flex;justify-content: center"><el-image fit="contain" style="height: 60vh;" :src="imgcode"></el-image></span>
                        <span slot="footer" class="dialog-footer">
                <el-button @click="visible = false">关 闭</el-button>
                <el-button type="primary"><a id="down" style="color: white" :href="imgcode" download>下 载</a></el-button>
              </span>
                    </el-dialog>
                </div>
            </div>
        </el-tab-pane>
        <el-tab-pane label="票商佣金">

            <div>
                <div class="page-header">获取更多佣金</div>
                <div class="page-header"><a href="http://xcx.ylhwlkj.com">申请票商</a>  <span>介绍</span></div>
                <div style="width: 60%">
                    <p>
                        我们的电影平台为电影爱好者们提供了一个全新的购票体验，其中的票商功能使得购票变得更加便捷和有趣。在我们的电影平台中，任何人都可以成为一个票商。当用户购买电影票时，票商可以抢单来出票，并在此过程中设置自己的佣金价格。
                    </p>
                    <p>
                        注册成为票商非常简单，您只需在电影平台注册并进行身份验证即可开始抢单。一旦您成为票商，您将有机会参与我们的各种电影票销售活动，并且有机会通过抢单获得佣金。通过设置自己的佣金价格，您可以根据自己的业务需求进行调整，以提高自己的利润。此外，我们的平台还将提供实时的抢单数据，使您可以更好地了解市场趋势并作出明智的决策。
                    </p>
                    <p>
                        对于用户而言，票商功能也提供了极大的便利。通过抢单出票，用户可以享受更快捷、更灵活、更优质的购票体验。此外，由于平台上的票商竞争激烈，用户还可以通过比较不同票商的佣金价格来选择最优惠的票价。
                    </p>
                    <p>
                        总之，票商功能为电影平台的用户和票商都提供了极大的便利和优势。我们希望通过这个功能，为电影爱好者们带来更加愉悦和便捷的购票体验，同时也希望更多的票商们能够加入到我们的平台中来，共同创造更好的电影生态。
                    </p>
                </div>
            </div>
        </el-tab-pane>
        <el-tab-pane label="星巴克分销">定时任务补偿</el-tab-pane>
        <el-tab-pane label="肯德基分销">定时任务补偿</el-tab-pane>
    </el-tabs>
</div>
<link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
<script src="https://unpkg.com/vue@2/dist/vue.js"></script>
<script src="https://unpkg.com/element-ui/lib/index.js"></script>
<script>
    new Vue({
        el: '#app3',
        data: function() {
            return {
                visible: false,
                imgcode:''
            }
        },
        methods:{
            gopiao(){
                window.location.href="xcx.ylhwlkj.com"
            },
            getimg(item) {
                var url = '/stores/qrcode';
                var data = {
                    poster:item.lphoto,
                    film_id:item.id,
                    type:'',
                    _token:"{{csrf_token()}}"
                };
                let that = this;
                $.ajax({
                    url: url,
                    type:'POST',
                    data: data,
                    success:function(res){
                        that.open(res)
                    }
                })
                // this.visible = true
            },
            open(res){
                this.imgcode = res
                this.visible = true
            }
        }
    })
</script>
<style>
    .data-item .data{color:#FD6B31;font-size:24px;}
    .total{margin-top: 30px}
    .total >p{margin-bottom: 5px;font-weight: 600;}
    .total .data{color:#FD6B31;}
    .col-form-label{text-align: right}
    .store_logo{
        width: 75px;
        height: 75px;
        border-radius: 100%;
    }
</style>
@endsection

<style>
    /*.row-title{font-size:18px;font-weight:500;margin-bottom:10px;}*/
    .data-item{text-align:center;}
    .data-number,.data-text{color:#787878}
    .data-text{margin-top:10px;padding-bottom:20px;}
    .data-number .num{font-size:24px;color:#5f5f5f;margin-right:5px;}
</style>
<?php
function formatMoney($money){
    return round($money / 10000 ,4);
}
?>

<h4 class="row-title">电影票数据统计</h4>
<div class="row">
    <div class="col-md-6">
        <div class="box">
            <div class="box-header">电影票销售数据</div>
            <div class="box-body d-flex">
                <div class="data-item col-md-4">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($ticketSaleMoney), false); ?></span>万</div>
                    <div class="data-text">销售总额</div>
                </div>
                <a href="/admin/user-orders?state=3" class="data-item col-md-4">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e($ticketSaleCount, false); ?></span>笔</div>
                    <div class="data-text"><u>累计订单量</u></div>
                </a>
                <div class="data-item col-md-4">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($ticketProfit), false); ?></span>万</div>
                    <div class="data-text">毛利总额</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box">
            <div class="box-header">电影票商家结款</div>
            <div class="box-body d-flex">
                <div class="data-item col-md-6">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($waitSettleMoney), false); ?></span>万</div>
                    <div class="data-text">应结总额</div>
                </div>

                <div class="data-item col-md-6">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($ticketStoreSettleMoney), false); ?></span>万</div>
                    <div class="data-text">已结总额</div>
                </div>
            </div>
        </div>
    </div>
</div>
<h4 class="row-title">商城数据统计</h4>
<div class="row">
    <div class="col-md-6">
        <div class="box">
            <div class="box-header">商城销售数据</div>
            <div class="box-body d-flex">
                <div class="data-item col-md-4">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($mallSaleMoney), false); ?></span>万</div>
                    <div class="data-text">销售总额</div>
                </div>
                <a href="/admin/orders?order_status=30" class="data-item col-md-4">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e($mallOrderCount, false); ?></span>笔</div>
                    <div class="data-text"><u>累计订单量</u></div>
                </a>
                <div class="data-item col-md-4">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($mallProfit), false); ?></span>万</div>
                    <div class="data-text">毛利总额</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box">
            <div class="box-header">商城商家结款数据</div>
            <div class="box-body d-flex">
                <div class="data-item col-md-6">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($mallWaitSettleMoney), false); ?></span>万</div>
                    <div class="data-text">应结总额</div>
                </div>
                <div class="data-item col-md-6">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($mallSettleMoney), false); ?></span>万</div>
                    <div class="data-text">已结总额</div>
                </div>
            </div>
        </div>
    </div>
</div>
<h4 class="row-title">影旅卡数据统计</h4>
<div class="row">
    <div class="col-md-6">
        <div class="box">
            <div class="box-header">影旅卡销售数据</div>
            <div class="box-body d-flex">
                <div class="data-item col-md-6">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($cardSaleMoney), false); ?></span>万</div>
                    <div class="data-text">销售总额</div>
                </div>
                <div class="data-item col-md-6">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($cardProfit), false); ?></span>万</div>
                    <div class="data-text">毛利总额</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box">
            <div class="box-header">影旅卡商家结款数据</div>
            <div class="box-body d-flex">
                <div class="data-item col-md-6">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($cardWaitSettleMoney), false); ?></span>万</div>
                    <div class="data-text">应结总额</div>
                </div>
                <div class="data-item col-md-6">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($cardSettleMoney), false); ?></span>万</div>
                    <div class="data-text">已结总额</div>
                </div>
            </div>
        </div>
    </div>
</div>
<h4 class="row-title">总毛利润数据统计</h4>
<div class="row">
    <div class="col-md-6">
        <div class="box">
            <div class="box-header">毛利总额</div>
            <div class="box-body d-flex">
                <div class="data-item">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($profitMoney), false); ?></span>万</div>
                    <div class="data-text">销售总额</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box">
            <div class="box-header">会员分润数据统计</div>
            <div class="box-body">
                <div class="data-item col-md-6">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($totalMemberProfit), false); ?></span>万</div>
                    <div class="data-text">分润总额（电影票+商城）</div>
                </div>
                <div class="data-item col-md-6">
                    <div class="data-icon"></div>
                    <div class="data-number"><span class="num"><?php echo e(formatMoney($memberProfit), false); ?></span>万</div>
                    <div class="data-text">已结总额</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <h4 class="row-title">会员排行</h4>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">消费最多的会员排行</a></li>
            <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">粉丝最多的会员排行</a></li>
          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="home">
                <table class="table">
                    <tr>
                        <td>排行</td>
                        <td>头像</td>
                        <td>昵称</td>
                        <td>累计消费金额</td>
                        <td>注册时间</td>
                    </tr>
                    <?php if($memberCostList): ?>
                        <?php $__currentLoopData = $memberCostList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($k+1, false); ?></td>
                            <td><img src="<?php echo e($member->avatar, false); ?>" width="45" alt=""></td>
                            <td><?php echo e($member->nickname, false); ?></td>
                            <td><?php echo e($member->cash_money, false); ?></td>
                            <td><?php echo e($member->created_at, false); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </table>
            </div>
            <div role="tabpanel" class="tab-pane" id="profile">
                <table class="table">
                    <tr>
                        <td>排行</td>
                        <td>头像</td>
                        <td>昵称</td>
                        <td>粉丝数</td>
                        <td>注册时间</td>
                    </tr>
                    <?php if($memberInviterList): ?>
                        <?php $__currentLoopData = $memberInviterList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($k+1, false); ?></td>
                            <td><img src="<?php echo e($member->avatar, false); ?>" width="45" alt=""></td>
                            <td><?php echo e($member->nickname, false); ?></td>
                            <td><?php echo e($inviterNumber[$member->id] ?? 0, false); ?></td>
                            <td><?php echo e($member->created_at, false); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </table>
            </div>
          </div>
    </div>
    <div class="col-md-6">
        <h4 class="row-title">销售排行</h4>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#home2" aria-controls="home" role="tab" data-toggle="tab">销售额最多的商家(商城)排行</a></li>
            <li role="presentation"><a href="#profile3" aria-controls="profile" role="tab" data-toggle="tab">商城单品销量排行</a></li>
            <li role="presentation"><a href="#profile2" aria-controls="profile" role="tab" data-toggle="tab">电影票出票最多的商家排行</a></li>

          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="home2">
                <table class="table">
                    <tr>
                        <td>排行</td>
                        <td>商家名称</td>
                        <td>商家类别</td>
                        <td>销售总额</td>
                    </tr>
                    <?php if($mallStoreTop): ?>
                        <?php $__currentLoopData = $mallStoreTop; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($k+1, false); ?></td>
                            <td><?php echo e($store->store_name, false); ?></td>
                            <td><?php echo e($storeCategory[$store->category_id] ?? '', false); ?></td>
                            <td><?php echo e($store->sale_money, false); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </table>
            </div>
            <div role="tabpanel" class="tab-pane" id="profile2">
                <table class="table">
                    <tr>
                        <td>排行</td>
                        <td>店铺名称</td>
                        <td>商家等级</td>
                        <td>订单量</td>
                        <td>出票金额</td>
                    </tr>
                    <?php if($outTicketStoreTop): ?>
                        <?php $__currentLoopData = $outTicketStoreTop; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                if(empty($storeInfoList[$store->store_id])){ continue; }
                            ?>
                        <tr>
                            <td><?php echo e($k+1, false); ?></td>
                            <td><?php echo e($storeInfoList[$store->store_id]->store_name, false); ?></td>
                            <td><?php echo e($storeLevel[$storeInfoList[$store->store_id]->store_level]??'', false); ?></td>
                            <td><?php echo e($store->out_ticket_count, false); ?></td>
                            <td><?php echo e($store->settle_money, false); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </table>
            </div>
            <div role="tabpanel" class="tab-pane" id="profile3">
                <table class="table">
                    <tr>
                        <td>排行</td>
                        <td>商品名称</td>
                        <td>单价</td>
                        <td>销量</td>
                    </tr>
                    <?php if($productList): ?>
                        <?php $__currentLoopData = $productList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$goods): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($k+1, false); ?></td>
                            <td><?php echo e($goods->title, false); ?></td>
                            <td><?php echo e($goods->sku_price, false); ?></td>
                            <td><?php echo e($goods->sale_num, false); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </table>
            </div>
          </div>
    </div>
</div>
<?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/custom/admin/statistics.blade.php ENDPATH**/ ?>
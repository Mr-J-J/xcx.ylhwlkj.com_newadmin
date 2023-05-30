<?php $__env->startSection('content'); ?>
    <style>
        .data-item .data{color:#FD6B31;font-size:24px;}
        .total{margin-top: 30px}
        .total >p{margin-bottom: 5px;font-weight: 600;}
        .total .data{color:#FD6B31;}
    </style>
    <div>
            <div class="page-header">小程序推广</div>
            <div class="page-header">公众号推广小程序  <span>示例</span></div>
            <div>
                <p>第一步：公众号关联小程序</p>
                <div class="tips-item">①进入公众号 → 广告与服务 → 小程序管理 → 关联小程序 (小程序APPID:<span class="copy-text"><?php echo e(config('wechat.mini_program.default1.app_id'), false); ?></span>)  <button type="button"  class="copy-btn btn btn-sm btn-outline-info">复制</button></div>
                <p>第二步: 配置公众号自定义菜单</p>
                <div class="tips-item">①配置菜单内容为“跳转到小程序”。</div>
                <div class="tips-item">②跳转到小程序首页路径：<span class="copy-text">pages/index/index?com_id=<?php echo e($store->id, false); ?></span>  <button type="button"   class="copy-btn btn btn-sm btn-outline-info">复制</button></div>
            </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/stores/gongzhong.blade.php ENDPATH**/ ?>
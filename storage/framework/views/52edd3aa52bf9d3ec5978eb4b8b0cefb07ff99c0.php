<div class="box box-info">
    <div class="box-body">
        <div class="page-header">公众号关联小程序</div>
        <div>
            <p>第一步：公众号关联小程序</p>
            <div class="tips-item">①进入公众号 → 广告与服务 → 小程序管理 → 关联小程序 (小程序APPID:<span class="copy-text"><?php echo e(config('wechat.mini_program.default1.app_id'), false); ?></span>)  <button type="button"  class="copy-btn btn btn-sm btn-outline-info">复制</button></div>
            <p>第二步: 配置公众号自定义菜单</p>
            <div class="tips-item">①配置菜单内容为“跳转到小程序”。</div>
            <div class="tips-item">②跳转到小程序首页路径：<span class="copy-text">pages/index/index?com_id=<?php echo e($store->id, false); ?></span>  <button type="button"   class="copy-btn btn btn-sm btn-outline-info">复制</button></div>
        </div>
    </div>
</div>

<script>
    
    var clipboard = new Clipboard('.copy-btn',{
        text:function(trigger){
            _text = $(trigger).prev('.copy-text').text()
            $.admin.toastr.success('内容已复制')
            return _text;
        }
    });
    clipboard.on('success', function(e) {
        e.clearSelection();
    });
</script><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/custom/card/setting.blade.php ENDPATH**/ ?>
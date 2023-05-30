<div class="box grid-box" >
    <div class="box-header">分销商影旅卡价格</div>
    <table class="table table-hover grid-table" style="width:50%">
        <tr>
        <th></th>
        <th>影旅卡名称</th>
        <th>卡余额</th>
        <th>成本价</th>
        <th>分销商价格</th>
        </tr>
        <?php $__currentLoopData = $cardList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td></td>
            <td><?php echo e($card->short_title, false); ?></td>
            <td><?php echo e($card->card_money, false); ?></td>
            <td><?php echo e($card->price, false); ?></td>
            <td><?php echo e($priceList[$card->id]??0, false); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table>
</div><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/custom/card/price.blade.php ENDPATH**/ ?>
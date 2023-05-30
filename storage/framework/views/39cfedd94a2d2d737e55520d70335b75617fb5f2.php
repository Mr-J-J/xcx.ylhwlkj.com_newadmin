<div>
    <span class="<?php echo e($elementClass, false); ?>" data-inserted="0" data-key="<?php echo e($key, false); ?>" data-name="<?php echo e($name, false); ?>"
          data-toggle="collapse" data-target="#grid-collapse-<?php echo e($name, false); ?>">
        <a href="javascript:void(0)"><i class="fa fa-angle-double-down"></i>&nbsp;&nbsp;<?php echo e($value, false); ?></a>
    </span>
    <template class="grid-expand-<?php echo e($name, false); ?>">
        <tr style='background-color: #ecf0f5;'>
            <td colspan='100%' style='padding:0 !important; border:0;'>
                <div id="grid-collapse-<?php echo e($name, false); ?>" class="collapse">
                    <div style="padding: 10px 10px 0 10px;" class="html">
                        <?php if($html): ?>
                            <?php echo $html; ?>

                        <?php else: ?>
                            <div class="loading text-center" style="padding: 20px 0px;">
                                <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
    </template>
</div>

<script>
    var expand = $('.<?php echo e($elementClass, false); ?>');

    <?php if($async): ?>

    var load = function (url, target) {
        $.get(url, function (data) {
            target.find('.html').html(data);
        });
    };

    expand.on('click', function (e) {
        var target = $(this);
        if (target.data('inserted') == '0') {
            var key  = target.data('key');
            var name = $(this).data('name');
            var row = $(this).closest('tr');

            row.after($('template.grid-expand-'+name).html());

            $(this).data('inserted', 1);

            load('<?php echo e($url, false); ?>'+'&key='+key, $('#grid-collapse-'+name));
        }

        $("i", this).toggleClass("fa-angle-double-down fa-angle-double-up");
    });

    $(document).on('pjax:click', '.collapse a.pjax, .collapse a.pjax', function (e) {
        console.log(11111);
        // load($(this).attr('href'), $(this).parent('.collapse'));
        e.preventDefault();
        return false;
    }).on('pjax:submit', '.collapse .box-header form', function (e) {
        // load($(this).attr('action')+'&'+$(this).serialize(), $(this).parent('.collapse'));
        return false;
    });

    <?php else: ?>

    expand.on('click', function () {

        if ($(this).data('inserted') == '0') {

            var name = $(this).data('name');
            var row = $(this).closest('tr');

            row.after($('template.grid-expand-'+name).html());

            $(this).data('inserted', 1);
        }

        $("i", this).toggleClass("fa-angle-double-down fa-angle-double-up");
    });

    <?php endif; ?>

    <?php if($expand): ?>
        expand.trigger('click');
    <?php endif; ?>
</script>

<?php if($loadGrid): ?>
<style>
    .collapse .grid-box .box-header:first-child {
        display: none;
    }
</style>
<?php endif; ?><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/admin/components/column-expand.blade.php ENDPATH**/ ?>
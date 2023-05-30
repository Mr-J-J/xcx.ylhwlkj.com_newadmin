<span class="grid-selector" data-toggle="modal" data-target="#<?php echo e($modal, false); ?>" key="<?php echo e($key, false); ?>" data-val="<?php echo e($original, false); ?>">
   <a href="javascript:void(0)" class="text-muted">
       <i class="fa fa-check-square-o"></i>&nbsp;&nbsp;
       <span class="text"><?php echo e($value, false); ?></span>
   </a>
</span>

<style>
    .belongsto.modal tr {
        cursor: pointer;
    }

    .belongsto.modal .box {
        border-top: none;
        margin-bottom: 0;
        box-shadow: none;
    }
    .belongsto.modal .loading {
        margin: 50px;
    }
</style>

<template>
    <div class="modal fade belongsto" id="<?php echo e($modal, false); ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 5px;">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><?php echo e(admin_trans('admin.choose'), false); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="loading text-center">
                        <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo e(admin_trans('admin.cancel'), false); ?></button>
                    <button type="button" class="btn btn-primary submit"><?php echo e(admin_trans('admin.submit'), false); ?></button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    var $modal = $('#<?php echo e($modal, false); ?>');
    var $related = null;

    var update = function (callback) {
        $.ajax({
            url: "<?php echo e($resource, false); ?>/" + $related.attr('key'),
            type: "POST",
            data: {
                '<?php echo e($name, false); ?>': selected.length ? selected : [''],
                _token: LA.token,
                _method: 'PUT'
            },
            success: function (data) {
                callback(data);
            }
        });
    };

<?php if($relation == \Encore\Admin\Grid\Displayers\BelongsTo::class): ?>

    var selected = null;

    var load = function (url) {
        $.get(url, function (data) {
            $modal.find('.modal-body').html(data);
            $modal.find('.select').iCheck({
                radioClass:'iradio_minimal-blue',
                checkboxClass:'icheckbox_minimal-blue'
            });
            $modal.find('.box-header:first').hide();

            $modal.find('input.select').each(function (index,    el) {
                if ($(el).val() == selected) {
                    $(el).iCheck('toggle');
                }
            });
        });
    };

    $modal.on('show.bs.modal', function (e) {
        $related = $(e.relatedTarget);
        selected = $related.data('val');
        load("<?php echo $url; ?>");
    }).on('ifChecked', 'input.select', function (e) {
        selected = $(this).val();
    }).find('.modal-footer .submit').click(function () {
        update(function (data) {
            $related.data('val', selected);
            $related.find('.text').html(data.display["<?php echo e($name, false); ?>"]);
            $related.find('a').toggleClass('text-green text-muted');

            setTimeout(function () {
                $related.find('a').toggleClass('text-green text-muted');
            }, 2000);

            $modal.modal('toggle');
            if(data.status){
                toastr.success(data.message);
            }else{
                toastr.error(data.message);
            }
            $.pjax.reload('#pjax-container');
        });
    });

<?php else: ?>

    var selected = [];

    var load = function (url) {
        $.get(url, function (data) {
            $modal.find('.modal-body').html(data);
            $modal.find('.select').iCheck({
                radioClass:'iradio_minimal-blue',
                checkboxClass:'icheckbox_minimal-blue'
            });
            $modal.find('.box-header:first').hide();

            $modal.find('input.select').each(function (index, el) {
                if ($.inArray($(el).val().toString(), selected) >=0 ) {
                    $(el).iCheck('toggle');
                }
            });
        });
    };

    $modal.on('show.bs.modal', function (e) {
        $related = $(e.relatedTarget);
        selected = $related.data('val').map(function (value) {
            return value.toString();
        });

        load("<?php echo $url; ?>");
    }).on('ifChecked', 'input.select', function (e) {
        var val = $(this).val().toString();
        if (selected.indexOf(val) < 0) {
            selected.push(val);
        }
    }).on('ifUnchecked', 'input.select', function (e) {
        var val = $(this).val().toString();
        var index = selected.indexOf(val);
        if (index !== -1) {
            selected.splice(index, 1);
        }
    }).find('.modal-footer .submit').click(function () {
        update(function (data) {
            $related.data('val', selected);
            $related.find('.text').html(data.display["<?php echo e($name, false); ?>"]);
            $related.find('a').toggleClass('text-green text-muted');

            setTimeout(function () {
                $related.find('a').toggleClass('text-green text-muted');
            }, 2000);

            $modal.modal('toggle');

            toastr.success(data.message);
        });
    });
<?php endif; ?>

    $modal.on('click', '.page-item a, .filter-box a', function (e) {
        load($(this).attr('href'));
        e.preventDefault();
    }).on('click', 'tr', function (e) {
        $(this).find('input.select').iCheck('toggle');
        e.preventDefault();
    }).on('submit', '.box-header form', function (e) {
        load($(this).attr('action')+'&'+$(this).serialize());
        e.preventDefault();
        return false;
    })

</script>
<?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/admin/grid/inline-edit/belongsto.blade.php ENDPATH**/ ?>
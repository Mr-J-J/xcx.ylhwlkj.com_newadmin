<?php $__env->startSection('content'); ?>
<style>
    .col-form-label{text-align: right}
    .store_logo{
        width: 75px;
        height: 75px;
        border-radius: 100%;
    }
</style>
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
                                <input type="text" readonly class="form-control-plaintext" value="<?php echo e($id, false); ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label  class="col-sm-3 col-form-label">分销商名称：</label>
                            <div class="col-sm-8">
                                <input type="text"  readonly class="form-control-plaintext" value="<?php echo e($store_name, false); ?>">
                            </div>
                        </div>
                       
                        <div class="form-group row">
                            <label  class="col-sm-3 col-form-label">分销商类型：</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext" value="<?php echo e($type, false); ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label  class="col-sm-3 col-form-label">手机号码：</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext" value="<?php echo e($phone, false); ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label  class="col-sm-3 col-form-label">品牌Logo：</label>
                            <div class="col-sm-8">
                                <div class="form-control-plaintext"><img src="<?php echo e($store_logo, false); ?>" class="store_logo " alt=""></div>
                                
                            </div>
                        </div>

                        <div class="form-group row">
                            <label  class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-8">
                                
                            </div>
                        </div>
                        
                    </div>
                </div>
              </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/stores/profile.blade.php ENDPATH**/ ?>
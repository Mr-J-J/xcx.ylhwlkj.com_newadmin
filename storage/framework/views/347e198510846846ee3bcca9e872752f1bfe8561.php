<?php $__env->startSection('content'); ?>
    
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
    <script src="https://unpkg.com/vue@2/dist/vue.js"></script>
    <script src="https://unpkg.com/element-ui/lib/index.js"></script>
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">海报-推广</h3>
        </div>
        <div id="appp">
            <el-tooltip class="item" effect="dark" content="参与出票获取更多佣金" placement="left-end">
                <el-link :underline="false" @click="gopiao">申请票商</el-link>
            </el-tooltip>

            <el-carousel height="35rem">
                <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <el-carousel-item height="30rem">
                    <div style="display: flex;justify-content: center;align-items: center;flex-direction: column">
                    <div class="box-body" style="height: 25rem;width: 18rem;">
                        <el-image style="height: 100%;width: 100%;" src="<?php echo e($item->lphoto, false); ?>" alt="" class="img-responsive">
                    </div>
                    <div class="box-footer">
                            <div @click="getimg(<?php echo e($item, false); ?>)">
                                <a href="#myPopup" class="btn btn-block btn-danger font1" style="margin-top: 0.5rem" data-rel="popup" data-position-to="window">

                                    生成
                                </a>
                            </div>
                    </div>
                </div>
                </el-carousel-item>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
    <script>
        new Vue({
            el: '#appp',
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
                        _token:"<?php echo e(csrf_token(), false); ?>"
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
                },
                down(){

                }
            }
        })
    </script>
    <style>
        .box-header{
            margin-top: 1rem;
        }
        .button-bottom{
            display: flex;

        }
        .box-footer{
            margin-top: 0.5rem;
        }
        .font1{
            width: 4rem;
            margin: 1rem;
        }
        .box-body img {
            /*width: 4rem;*/
            /*height: 10rem;*/
            background: cover;
        }
        .box-solid{
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .img1{
            flex-direction: column;
            display: none;
            justify-content: center;
            align-items: center;
            /*disabled:disabled;*/
            /*margin-top: 5rem;*/
            top: 3rem;
            width: 100%;
            /*display: flex;*/
            position: absolute;
            background: #00000085;
            padding: 3rem;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /data/wwwroot/xcx.ylhwlkj.com/resources/views/stores/img.blade.php ENDPATH**/ ?>
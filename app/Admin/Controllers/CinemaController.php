<?php

namespace App\Admin\Controllers;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\ApiModels\Wangpiao\CinemasBrand;
use App\ApiModels\Wangpiao\Cinema;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Http\Request;
class CinemaController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影院管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Cinema());
        $grid->filter(function($filter){
            $filter->like('cinema_name','影院名称');
        });

        $grid->column('id', __('影院ID'));
        $grid->column('brand_id', __('院线'))->display(function(){
            // dd($this->brand->toArray());
            return $this->brand['brand_name'];
        });;
        
        $grid->column('cinema_name', __('影院名称'));
        
        $grid->column('phone', __('电话'));
        // $grid->column('city_code', __('City code'));
        // $grid->column('region_name', __('所在城市'));
        $grid->column('address', __('地址'));
        $grid->actions(function($actions){
            $actions->disableDelete();
            // $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Cinema::findOrFail($id));

        $show->field('id', '影院ID');
        $show->field('cinema_name', '影院名称');
        $show->field('brand_id', '院线');
        $show->field('longitude', '经度');
        $show->field('latitude', '纬度');
        $show->field('schedule_close_time', __('Schedule close time'));
        $show->field('phone', '电话');
        // $show->field('city_code', __('City code'));
        // $show->field('region_name', __('Region name'));
        $show->field('address', '影院地址');
        // $show->field('lowest_price', __('Lowest price'));
        // $show->field('show_time', __('Show time'));
        // $show->field('created_at', __('Created at'));
        // $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Cinema());

        $list = CinemasBrand::brandsOptions()->toArray();
        
        $brandArr = array_combine(array_column($list,'id'),array_column($list,'brand_name'));
        
        // $form->disableReset();
        $form->display('id', __('影院ID'));
        $form->select('brand_id',__('所属院线'))->options($brandArr);        
        $form->text('cinema_name', __('影院名称'));
        $form->display('schedule_close_time', __('场次关闭时间'));
        $form->text('phone', __('影院电话'));
        // $form->display('region_name', __('城市'));
        $form->text('address', __('所在地址'));
        $form->display('lowest_price', __('最低价'));
        $form->display('show_time', __('开始时间'));
        // $form->ignore(['province']);
        
        return $form;
    }

    public function getList(Request $request){
        $cityId = $request->input('city_id','');
        $q = $request->get('q');
        return Cinema::search($cityId)->where('cinema_name','like',"%$q%")->orderBy('id','asc')->paginate(null, ['id', 'cinema_name as text']);     
    }
}

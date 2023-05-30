<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\ApiModels\Wangpiao\Film;

class FilmController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '影片列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Film());
        // $allFilm = Film::selectRaw('max(infoid) as infoid')->groupBy('id')->pluck('infoid');
        
        $grid->model()->orderBy('open_time','desc');
        
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableActions();
        $grid->column('id', __('Id'));
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('id','影片ID');
            $filter->like('show_name','影片名称');
        });
        // $grid->column('cinema_id', __('Cinema id'));
        // $grid->column('city_code', __('City code'));
        // $grid->column('show_name_en', __('Show name en'));
        $grid->column('show_name', '影片名称');
        // $grid->column('date_type', '影片状态')->using([1=>'即将上映',2=>'正在热映'])->label([
        //     1=>'default',
        //     2=>'warning'
        // ]);
        // $grid->column('remark', __('Remark'));
        // $grid->column('highlight', __('Highlight'));
        // $grid->column('country', __('Country'));
        // $grid->column('poster', __('Poster'));
        // $grid->column('hphoto', __('Hphoto'));
        // $grid->column('lphoto', __('Lphoto'));
        $grid->column('duration', '影片放映时间');
        $grid->column('open_time', '上映时间')->display(function(){
            if($this->open_time > 0){
                return $this->open_time;
            }
            return '-';
        });
        // $grid->column('mprice', '最低价');
        // $grid->column('grade_num', __('Grade num'));
        // $grid->column('trailerList', __('TrailerList'));
        $grid->column('type', '放映类型');
        // $grid->column('show_version_list', __('Show version list'));
        // $grid->column('description', __('Description'));
        $grid->column('language', '语言');
        // $grid->column('show_mark', __('Show mark'));
        $grid->column('leading_role', '主演');
        $grid->column('director', '导演');
        
       
        // $grid->column('updated_at', __('Updated at'));
        // $grid->column('created_at', __('Created at'));

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
        $show = new Show(Film::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('cinema_id', __('Cinema id'));
        $show->field('city_code', __('City code'));
        $show->field('show_name_en', __('Show name en'));
        $show->field('remark', __('Remark'));
        $show->field('highlight', __('Highlight'));
        $show->field('country', __('Country'));
        $show->field('show_name', __('Show name'));
        $show->field('poster', __('Poster'));
        $show->field('hphoto', __('Hphoto'));
        $show->field('lphoto', __('Lphoto'));
        $show->field('duration', __('Duration'));
        $show->field('mprice', __('Mprice'));
        $show->field('grade_num', __('Grade num'));
        $show->field('trailerList', __('TrailerList'));
        $show->field('type', __('Type'));
        $show->field('open_time', __('Open time'));
        $show->field('show_version_list', __('Show version list'));
        $show->field('description', __('Description'));
        $show->field('language', __('Language'));
        $show->field('show_mark', __('Show mark'));
        $show->field('leading_role', __('Leading role'));
        $show->field('director', __('Director'));
        $show->field('date_type', __('Local type'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_at', __('Created at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Film());

        $form->number('cinema_id', __('Cinema id'));
        $form->number('city_code', __('City code'));
        $form->text('show_name_en', __('Show name en'));
        $form->text('remark', __('Remark'));
        $form->textarea('highlight', __('Highlight'));
        $form->text('country', __('Country'));
        $form->text('show_name', __('Show name'));
        $form->text('poster', __('Poster'));
        $form->text('hphoto', __('Hphoto'));
        $form->text('lphoto', __('Lphoto'));
        $form->text('duration', __('Duration'));
        $form->decimal('mprice', __('Mprice'))->default(0.00);
        $form->number('grade_num', __('Grade num'));
        $form->textarea('trailerList', __('TrailerList'));
        $form->text('type', __('Type'));
        $form->number('open_time', __('Open time'));
        $form->text('show_version_list', __('Show version list'));
        $form->textarea('description', __('Description'));
        $form->text('language', __('Language'));
        $form->text('show_mark', __('Show mark'));
        $form->text('leading_role', __('Leading role'));
        $form->text('director', __('Director'));
        // $form->switch('date_type', __('Local type'));

        return $form;
    }
}

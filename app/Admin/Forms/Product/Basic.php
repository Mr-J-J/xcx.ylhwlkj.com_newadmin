<?php

namespace App\Admin\Forms\Product;

use App\Admin\Selectable\SelectMallStore;
use App\MallModels\Category;
use Illuminate\Http\Request;
use Encore\Admin\Widgets\Form;
use Illuminate\Support\Arr;

class Basic extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = '基本信息';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        //dump($request->all());

        admin_success('Processed successfully.');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $category = Arr::pluck(Category::getOptions(),'title','id');
        $category = Arr::prepend($category, '默认分类');
        $this->select('category_id','所属分类')->options($category)->rules('required',['required'=>'请选择商品分类']);
        $this->text('title','标题')->rules('required',['required'=>'请填写商品标题']);
        $this->text('subtitle','介绍');
        $this->text('sort','排序')->default(0)->setWidth(2);
        $this->radio('state','是否上架')->options(['暂不上架','立即上架']);
        $this->image('image','缩略图');
        $this->belongsTo('store_id', SelectMallStore::class, '所属商家')->rules('required',['required'=>'请选择商家'])->help('指定核销订单的商家');
        
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return [
            'name'       => 'John Doe',
            'email'      => 'John.Doe@gmail.com',
            'created_at' => now(),
        ];
    }
}

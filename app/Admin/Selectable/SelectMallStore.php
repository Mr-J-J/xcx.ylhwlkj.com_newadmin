<?php
namespace App\Admin\Selectable;

use Encore\Admin\Grid;
use App\MallModels\Stores;
use Encore\Admin\Grid\Tools;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;
use EasyWeChat\Kernel\Support\Arr;


class SelectMallStore extends Selectable
{
    public $model = Stores::class;

    protected $key = 'user_id';

    public function make(){
        $this->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('user.mobile','手机号码');
        });
        $this->column('store_name','商家名称');
        $this->column('user.avatar','头像')->image('',45);
        $this->column('user.nickname','微信昵称');
        $this->column('user.mobile','手机号');
        $this->column('limit_money','剩余结款金额')->display(function(){
            return sprintf('%.2f',($this->freeze_money - $this->settle_money));
        });
    }

    public function renderFormGrid($values)
    {
        $this->make();

        $this->appendRemoveBtn(false);
        
        // $this->model()->whereKey(Arr::wrap($values));
        $this->model()->where($this->key,$values);
        
        $this->disableFeatures()->disableFilter();

        if (!$this->multiple) {
            $this->disablePagination();
        }

        $this->tools(function (Tools $tools) {
            $tools->append(new Grid\Selectable\BrowserBtn());
        });

        return $this->grid;
    }
}
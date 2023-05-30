<?php
namespace App\Admin\Selectable;

use App\CardModels\RsStores;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Tools;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class SelectCardStore extends Selectable
{
    public $model = RsStores::class;

    protected $key = 'id';

    public function make(){
        $this->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('phone','手机号码');
        });
        $this->column('store_logo','头像')->image('',45);
        $this->column('store_name','商家名称');
        $this->column('phone','手机号');
        $this->column('balance','可结算金额');
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
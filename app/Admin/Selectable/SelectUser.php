<?php
namespace App\Admin\Selectable;

use App\Models\TicketUser as Users;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class SelectUser extends Selectable
{
    public $model = Users::class;

    public function make(){
        $this->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('mobile','手机号码');
        });
        $this->column('avatar','头像')->image('',45);
        $this->column('mobile','手机号');
        $this->column('nickname','姓名');
    }
}
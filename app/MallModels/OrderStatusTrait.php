<?php

namespace App\MallModels;

/**
 * 订单状态
 */
trait OrderStatusTrait
{
    /**
     * 是否可以取消
     *
     * @return bool
     */
    public function canCancel(){

        return true;
    }

    /**
     * 是否可以评价
     *
     * @return bool
     */
    public function canComment(){
        return true;
    }
}

<?php

namespace App\Admin\Actions\Order;

use App\Models\store\StoreOfferOrder;
use App\Models\UserOrder;

use Encore\Admin\Actions\RowAction;

class ViewOffer extends RowAction
{
    protected $selector = '.view-offer';

    
    public function name(){
        return '查看竞价';
    }
    
    
    public function render()
    {
        $offerOrder = StoreOfferOrder::getOrderByOrderNo($this->row['order_no']);
        $id = '';
        if($offerOrder){
            $id = $offerOrder->id;
        }
        $href = '/admin/offer-orders/'.$id;
        return "<a href='{$href}' class='btn btn-twitter btn-xs {$this->getElementClass()}'>{$this->name()}</a>";
        
    }
}
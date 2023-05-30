<?php

namespace App\Admin\Actions\Order;

use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;
use App\Models\store\StoreOfferOrder;

class Offer extends RowAction
{
    protected $selector = '.offer';
      
    public function handle(StoreOfferOrder $order)
    {
        // $request ...

        return $this->response()->success('Success message...')->refresh();
    }

    public function modal(){

    }

    public function render()
    {
        if ($href = $this->href()) {
            return "<a href='{$href}' class='btn btn-twitter btn-xs {$this->getElementClass()}'>{$this->name()}</a>";
        }

        $this->addScript();

        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='btn btn-twitter btn-xs  %s' {$attributes}>%s</a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );
        
    }
}
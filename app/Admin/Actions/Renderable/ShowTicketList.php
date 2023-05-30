<?php
namespace App\Admin\Actions\Renderable;

use Encore\Admin\Widgets\Table;

use Illuminate\Contracts\Support\Renderable;
use App\Admin\Actions\Pft\DeleteTicketAction;

class ShowTicketList implements Renderable
{
    public function render($key = null)
    {
        
        $detail = \App\UUModels\UUScenicSpotInfo::find((int)$key);
        $ticketList = array();     
        if($detail){
            $ticketList = $detail->ticketList()->orderBy('UUaid')->get(['id','UUtitle','UUaid','UUid','UUlid','UUstatus']);                
        }
        $daction = new DeleteTicketAction();
        $list = array();
        
        if(!empty($ticketList)){
            foreach($ticketList as $item){
                $arr = array();
                $arr[] = "{$item->UUaid}|{$item->UUlid}|{$item->UUid}";
                $arr[] = $item->UUtitle;
                $arr[] = $item->getStatusTxt();
                $arr[] = (new DeleteTicketAction())->setGrid(new \Encore\Admin\Grid(new \App\UUModels\UUScenicSpotTicket()))->setRow($item);
                $list[] = $arr;                
            }            
        }
        
        
        // return (new Table(['产品ID', '名称', '状态','操作'], $list))->render();
        return view('custom.pft.ticket_list',compact('list'));
    }
}
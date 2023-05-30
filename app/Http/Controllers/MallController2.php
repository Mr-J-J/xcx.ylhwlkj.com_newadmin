<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use App\MallModels as M;
use App\MallModels\Activite;
use App\MallModels\Category;
use Illuminate\Http\Request;
use App\MallModels\ProductComment;
use Illuminate\Support\Facades\DB;

/**
 * 吃喝玩乐
 */
class MallController extends Controller
{
    
    protected $cityCode = '110100';
    
    public function __construct(){
        Setting::getSettings();
        $this->getCityId(request('city_code',''));
    }

    public function productDetail(Request $request){
        $productId = $request->input('id',0);
        $product = M\Product::with(['content','sku','images'])->where('id',$productId)->first();
        
        if(empty($product)){
            return $this->error('商品已下架');
        }
        $product->makeHidden(['virtual_sale_num']);
        $product->sale_num += $product->virtual_sale_num;
        $aboutus = M\StoreRegister::find(1);
        $product->price_tips = $aboutus->price_tips;
        $product->storage = 0;
        $product->limit_purchase = 0;
        if($product->sku){
            foreach($product->sku as $item){
                $item->sale_num+=$product->virtual_sale_num;
                if($product->sku_id == $item->id){
                    $product->storage = $item->storage;
                    $product->limit_purchase = $item->limit_purchase;
                }
            }
        }
        return $this->success('',$product);

    }

    public function productComment(Request $request){
        $productId = $request->input('id',0);               
        $list = ProductComment::getCommentList($productId);
        return $this->success('',$list);
    }

    public function commentDetail(Request $request){
        $commentId = $request->input('id',0);
        $comment = ProductComment::where('id',$commentId)->first();
        if(empty($comment)){
            return $this->error('评价内容不存在');
        }
        $comment->makeHidden(['updated_at','user']);
        $comment->avatar = $comment->user->avatar;
        $comment->nickname = $comment->user->nickname;
        
        $product = \App\MallModels\Product::where('id',$comment->product_id)->first();
        $comment->product_img = $product ?$product->image:'';
        $comment->product_title =  $product ?$product->title:'';
        
        return $this->success('',$comment);
    }

    /**
     * 商品列表
     *
     * @param Request $request
     * @return void
     */
    public function productList(Request $request){
       $categoryId = (int) $request->input('category_id',0);
       $orderBy = (int) $request->input('ordering',0); // 0热卖降序 1价格升序 2价格升序
       $keyword = (string) $request->input('keyword','');
       $tagId = (int) $request->input('tag_id',0);
       $limit = (int) $request->input('limit',10);
       $lng = $request->input('lng','');
       $lat = $request->input('lat','');
       $storeList = array();
       $storeIds = array();
       if($orderBy == 3){
            $orderBy = 1;
            if(!empty($lat) && !empty($lng)){
                $lng = round($lng,6);
                $lat = round($lat,6);
                $slist = M\Stores::getStoreList($this->cityCode,$lat,$lng);
                foreach($slist as $item){
                    $item->distance = $item->distance?M\Stores::formatDistance($item->distance):'';
                    $storeList[$item->id] = $item->toArray();               
                }
            }
       }
       
       $storeIds = array_keys($storeList);
       
       if($tagId == 0 && $categoryId == 0) return $this->success('缺少商品分类');

       $title = '';
       $cid = 0;
       if($tagId){
           $cid = $tagId;
           $title = Activite::where('id',$tagId)->value('title');
       }elseif($categoryId){
           $cid = $categoryId;
           $title = Category::where('id',$categoryId)->value('title');
       }
       if(empty($storeIds)){
            $orderByArr = ['id desc','sku_price asc','sku_price desc'];
            $list = M\Product::city($this->cityCode)
                    ->when($tagId,function($query,$tagId){
                        return $query->tag($tagId);
                    })
                    ->when($categoryId,function($query,$categoryId){
                        return $query->category($categoryId);
                    })
                    ->title($keyword)
                    ->where('state',M\Product::STATE_SALE)
                    ->orderByRaw(DB::raw($orderByArr[$orderBy]))
                    ->paginate($limit);
       }else{
            $list = M\Product::city($this->cityCode)
                    ->when($tagId,function($query,$tagId){
                        return $query->tag($tagId);
                    })
                    ->when($categoryId,function($query,$categoryId){
                        return $query->category($categoryId);
                    })
                    ->title($keyword)
                    ->where('state',M\Product::STATE_SALE)
                    ->orderByRaw('FIELD(store_id,'.implode(',',$storeIds).')')
                    ->paginate($limit);
       }
        foreach($list as $item){
            $item->makeHidden(['virtual_sale_num']);
            $item->sale_num+= $item->virtual_sale_num;
            $item->distance = !empty($storeList[$item->store_id])?$storeList[$item->store_id]['distance']:'';
        }
        $list = collect(['category_id'=>$cid,'category_title'=>$title])->merge($list);                
       return $this->success('',$list);
    }

    /**
     * 首页菜单列表
     *
     * @param Request $request
     * @return void
     */
    public function categoryList(Request $request){
        $isnav = (bool)$request->input('isnav',0); //是否菜单
        $istree = (bool)$request->input('istree',0); //是否分类树
        $categoryId = (int)$request->input('category_id',0);
        $list = M\Category::getList($categoryId,$isnav);
        if($istree){
            foreach($list as $cate){
                $cate->child =  M\Product::select(['image','title','id','category_id'])->where('state',M\Product::STATE_SALE)->category($cate->id)->get();
            }
        }
        return $this->success('',$list);
    }

    /**
     * 首页活动列表
     *
     * @param Request $request
     * @return void
     */
    public function activeList(Request $request){
        $list = M\Activite::getList();
        return $this->success('',$list);
    }

    /**
     * 商家入驻
     *
     * @return void
     */
    public function storeRegister(){
        $aboutus = M\StoreRegister::find(1);
        $partners = M\Partner::get();
        return $this->success('',compact('aboutus','partners'));
    }

    protected function getCityId($city_code){
        if($city_code == '') return '';
        $areaList = cache('getMallCityIdByCode',false);         
        if(!$areaList){
            $areaList = M\Region::where('city_level',3)->orderBy('city_code')->pluck('parent_city_code','city_code')->toArray();            
            cache(['getMallCityIdByCode'=>$areaList,86400*3]);
        }
        $this->cityCode = $areaList[$city_code]??$city_code;
    }

    
}

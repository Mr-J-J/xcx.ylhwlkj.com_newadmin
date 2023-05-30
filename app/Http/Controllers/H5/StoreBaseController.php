<?php
namespace App\Http\Controllers\H5;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StoreBaseController extends Controller
{
    
    protected $store;
    public function __construct(){
        parent::__construct();
        $this->store = Auth::guard('stores')->user();  
        if($this->store && $this->store->id == 19){
            // $this->store = Store::where('id',25)->first();
        }
    }
}

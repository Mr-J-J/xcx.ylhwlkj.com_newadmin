@extends('layouts.app')

@section('content')
<style>
    .table .thumb-img{width:120px;height:75px;margin-right:15px;}
    .table .images{width:100%;height:100%;}
    .table .price {color:#FD6B31;font-size:24px;}
</style>
<div class="page-header">影旅卡列表</div>
<div class="row">
    <div class="table-list table-responsive m-4">
        <table class="table">
            <thead>
                <tr>
                  <th scope="col">影旅卡类型</th>
                  <th scope="col">成本价</th>
                  <th scope="col">商城价</th>
                  <th scope="col">操作</th>
                </tr>
              </thead>
              <tbody>
                  @foreach($list as $card)
                    <tr>
                        <td>
                            <div class="d-flex">
                                <div class="thumb-img"><img src="{{$card->image}}" class="images"></div>
                                <div class="info">
                                    <div class="title">{{$card->short_title}}</div>
                                    <div class="price">{{$card->card_money}}元</div>
                                </div>
                            </div>
                            <div class="tips text-muted">{{$card->short_title}}优惠由 <span class="text-body">{{$store_name}}</span>赞助</div>
                        </td>
                        <td>{{$card->price}}</td>
                        <td>
                            <div>{{$priceList[$card->id]??0.00}}</div>
                            <div class="tips text-muted">商城价不得低于成本价</div>
                        </td>
                        <td>
                            <span class="btn-edit btn-link"  data-toggle="modal" data-target="#staticBackdrop" data-saleprice="{{$priceList[$card->id]??0.00}}" data-id="{{$card->id}}" data-title="{{$card->short_title}}" data-price="{{$card->price}}">设置商城价</span>
                        </td>
                    </tr>
                  @endforeach
                
              </tbody>
          </table>
    </div>
    <div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="staticBackdropLabel">商城价设置</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form  id="modal-form" method="post" action="/stores/card" onsubmit="return false;">
                    @csrf
                    <input type="hidden" name="id" value="" class="id">
                    <div class="form-group row">
                      <label for="recipient-name" class="col-sm-3 col-form-label">卡片类型</label>
                      <div class="col-sm-8"><input type="text" class="form-control cardtype" disabled></div>
                    </div>
                    <div class="form-group row">
                      <label for="message-text" class="col-sm-3 col-form-label">成本价</label>
                      <div class="col-sm-8"><input type="text" class="form-control cardprice" disabled></div>
                    </div>
                    <div class="form-group row">
                        <label for="message-text" class="col-sm-3 col-form-label">商城价</label>
                        <div class="col-sm-8"><input type="text" class="form-control cardsaleprice" name="saleprice"></div>
                    </div>  
              </form>                  
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light modal-btn" style="width:90px;" data-dismiss="modal"> 取 消 </button>
              <button type="button" class="btn btn-primary submit modal-btn"> 确 定 </button>
            </div>
          </div>


        </div>
      </div>
</div>
<script>
    $(function(){
        $('#staticBackdrop').on('show.bs.modal', function (event) {          
            var button = $(event.relatedTarget) // Button that triggered the modal
            var rowId = button.data('id') // Extract info from data-* attributes
            var title = button.data('title') // Extract info from data-* attributes
            var price = button.data('price') // Extract info from data-* attributes
            var saleprice = button.data('saleprice')             
            var modal = $(this)
            modal.find('.modal-body .cardtype').val(title)
            modal.find('.modal-body .cardprice').val(price)
            modal.find('.modal-body .cardsaleprice').val(saleprice)
            modal.find('.modal-body .id').val(rowId)
            $('.modal-footer .submit').off('click');
            $('.modal-footer .submit').on('click',function(){
                modal.modal('hide')
              $.ajax({
                url: "/stores/card",
                type:'POST',
                data: $('#modal-form').serialize(),
                success:function(res){
                  
                  toast(res.status,res.message);
                  $.pjax.reload('#pjax-container');
                //   location.reload()
                }
              })
            })
        })
    })
</script>
@endsection

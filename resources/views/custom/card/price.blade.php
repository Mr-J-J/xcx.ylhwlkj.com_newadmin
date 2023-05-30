<div class="box grid-box" >
    <div class="box-header">分销商影旅卡价格</div>
    <table class="table table-hover grid-table" style="width:50%">
        <tr>
        <th></th>
        <th>影旅卡名称</th>
        <th>卡余额</th>
        <th>成本价</th>
        <th>分销商价格</th>
        </tr>
        @foreach($cardList as $card)
        <tr>
            <td></td>
            <td>{{$card->short_title}}</td>
            <td>{{$card->card_money}}</td>
            <td>{{$card->price}}</td>
            <td>{{$priceList[$card->id]??0}}</td>
        </tr>
        @endforeach
    </table>
</div>
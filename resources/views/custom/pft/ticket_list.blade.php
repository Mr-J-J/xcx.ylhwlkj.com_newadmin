<table class="table ">
    <thead>
    <tr>
                    <th>产品ID</th>
                    <th>名称</th>
                    <th>状态</th>
                    <th>操作</th>
            </tr>
    </thead>
    <tbody>
        @php
            $script = '';
        @endphp
        @foreach($list as $k=>$item)
            @php
                if(!$k){
                    $script = $item[3]->addScript();
                }
            @endphp
        <tr>
                <td>{{$item[0]}}</td>
                <td>{{$item[1]}}</td>
                <td>{{$item[2]}}</td>
                <td>{!!$item[3]->render()!!}</td>
            </tr>
            @endforeach
        </tbody>
</table>
<script>
    {!! $script !!}
</script>



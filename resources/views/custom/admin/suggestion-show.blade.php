<p>用户手机：{{$phone}} ({{$user_type==1?'商家':'用户'}})</p>
<p>反馈类型：{{$type}}</p>
<p>反馈时间：{{$created_at}}</p>
<p>反馈内容：{{$content}}</p>
<p>反馈图片：
    <div class="row">
        @foreach($images as $img)
        <div class="col-md-3">
            <a href="{{$img}}" target="_blank"><img src="{{$img}}"></a>
        </div>
        @endforeach
    </div>
</p>
@extends('layouts.app')

@section('content')
{{--    输出文章--}}
    <div class="box">
        <div class="box-title">
            {{$title}}
        </div>
        <div class="content">
            {!!$content!!}
        </div>
    </div>
    <style>
        .box{
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .box-title{
            font-size: 1.5rem;
        }

    </style>
@endsection

@extends('core::frontend.default')

@section('content')
    <div id="blog-author" class="container p-0">
        <div class="row justify-content-md-center">
            <div class="col-lg-4">
                <h2>{{$author->author}}</h2>
            </div>
        </div>
        <div class="row justify-content-md-center">
            <div class="col-lg-8">
                <div class="container">
                    @foreach($posts as $post)
                        <div class="row card">
                            <div class="container">
                                <div class="row d-flex align-items-center">
                                    <div class="col-lg-7">
                                        <div class="label-title">
                                            <a href="{{route_i18n('blog',$post->getAttribute('slug'))}}">{{$post->getAttribute('title')}}</a>
                                        </div>
                                        <div class="label-date">
                                            <i class="fa fa-clock-o"></i>{{new \Carbon\Carbon($post->getAttribute('date'))}}
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        @include('core::partials.img',[
                                            'media'=>isset($media[$post->getAttribute('type')])?
                                            $media[$post->getAttribute('type')]->present('asset'):null,
                                            'alt'=>$post->getAttribute('title'),
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                        <div class="row text-right p-1">
                            <ul class="col search-tags social-icon">
                                <li class="d-block my-1"><a href="{{route('rss',
                                ['type'=>'author','slug'=>$author->author])}}"
                                                            target="_blank"><i class="fa fa-rss"></i></a>
                                </li>
                            </ul>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection
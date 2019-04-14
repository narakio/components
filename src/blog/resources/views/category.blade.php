@extends('core::frontend.default')

@section('content')
    <div id="blog-category" class="container p-0">
        <div class="row">
            <div id="blog-category-list" class="col-lg-8 col-md-12">
                <div class="container">
                    <div class="row">
                        <div id="blog-cat-featured" class="row">
                            <div class="cat-img">
                                @include('core::partials.img',[
                                    'media'=>isset($media[$featured->getAttribute('type')])?
                                    $media[$featured->getAttribute('type')]->present('asset'):null,
                                    'alt'=>$featured->getAttribute('title')
                                ])
                            </div>
                            <div class="cat-title">
                                <h3>
                                    <a href="{{route_i18n('blog',$featured->getAttribute('slug'))}}">{{$featured->getAttribute('title')}}</a>
                                </h3>
                            </div>
                            <div class="cat-date">
                                {!! trans('pages.blog.written_by',[
                                'date'=>new \Carbon\Carbon($featured->getAttribute('date')),
                                'author'=>sprintf('<a href="%s">%s</a>',route_i18n('blog.author',$featured->getAttribute('author')),$featured->getAttribute('author'))
                                ])!!}
                            </div>
                            <div class="cat-excerpt">
                                {{$featured->getAttribute('excerpt')}}
                            </div>
                        </div>
                        @foreach($posts as $post)
                            <div class="row blog-cat-list-item">
                                <div class="container p-0">
                                    <div class="row d-flex align-items-center">
                                        <div class="col-lg-8 col-md-12 p-0">
                                            <div class="cat-title">
                                                <h5>
                                                    <a href="{{route_i18n('blog',[$post->getAttribute('slug')])}}">{{$post->getAttribute('title')}}</a>
                                                </h5>
                                            </div>
                                            <div class="cat-date">
                                                {!! trans('pages.blog.written_by',[
                                                'date'=>new \Carbon\Carbon($post->getAttribute('date')),
                                                'author'=>sprintf('<a href="%s">%s</a>',route_i18n('blog.author',$post->getAttribute('author')),$post->getAttribute('author'))
                                                ])!!}
                                            </div>
                                            <div class="cat-excerpt">
                                                {{$post->getAttribute('excerpt')}}
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-12">
                                            <div class="cat-img">
                                                @include('core::partials.img',[
                                                    'media'=>isset($media[$post->getAttribute('type')])?
                                                    $media[$post->getAttribute('type')]->present('asset'):null,
                                                    'alt'=>$post->getAttribute('title')
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="row blog-cat-list-item p-0 my-0">
                            <ul class="search-tags social-icon p-0">
                                <li class="d-block my-1"><a href="{{route('rss', ['type'=>'category','slug'=>$featured->getAttribute('cat')])}}" target="_blank"><i class="fa fa-rss"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div id="blog-category-right" class="col-lg-4 col-md-12">
                <div class="container">
                    <div class="row">
                        <h5 class="bordered">
                            <span>{{trans('pages.blog.most_viewed_in',['category'=>$featured->getAttribute('cat')])}}</span>
                        </h5>
                    </div>
                    @foreach($mvps as $mvp)
                        <div class="row">
                            <div class="container p-0">
                                <div class="row d-flex align-items-center">
                                    <div class="col-lg-8 col-sm-6 p-0">
                                        <div class="cat-title">
                                            <h6>
                                                <a href="{{route_i18n('blog',$mvp->getAttribute('slug'))}}">{{$mvp->getAttribute('title')}}</a>
                                            </h6>
                                        </div>
                                        <div class="cat-date">
                                            {{new \Carbon\Carbon($mvp->getAttribute('date'))}}
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 p-0">
                                        @include('core::partials.img',[
                                             'media'=>isset($mvpImages[$mvp->getAttribute('type')])?
                                             $mvpImages[$mvp->getAttribute('type')]->present('thumbnail'):null,
                                             'alt'=>$mvp->getAttribute('title'),
                                             'format'=>\Naraki\Media\Models\MediaImgFormat::THUMBNAIL
                                         ])
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
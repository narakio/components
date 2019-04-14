@extends('core::frontend.default')

@section('content')
    <section id="blog-featured">
        <div class="row">
            @if(isset($posts['featured'][0]))
            <div id="blog-featured-carousel" class="col-lg-7 col-md-12">
                <div id="carousel-home" class="carousel slide carousel-fade" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#carousel-home" data-slide-to="0" class="active"></li>
                        <li data-target="#carousel-home" data-slide-to="1"></li>
                        <li data-target="#carousel-home" data-slide-to="2"></li>
                    </ol>
                    <div class="featured-content carousel-inner">
                        @for($i=0;$i<3;$i++)
                            <div class="carousel-item {{($i==0)?'active':''}}">
                                <div class="carousel-featured">
                                    <div class=carousel-featured-content">
                                        <a class="fc-cat badge-success"
                                           href="{{route_i18n('blog',$posts['featured'][$i]['cat'])}}">{{trans(sprintf('pages.blog.category.%s',$posts['featured'][$i]['cat']))}}</a>
                                        <h2 class="fc-title">
                                            <a href="{{route_i18n('blog',$posts['featured'][$i]['slug'])}}">{{$posts['featured'][$i]->present('title')}}</a>
                                        </h2>
                                        <span class="fc-date">{{$posts['featured'][$i]->present('date')}}</span>
                                    </div>
                                </div>
                                @if(isset($media[$posts['featured'][$i]->getAttribute('type')]))
                                @include('core::partials.img',[
                                    'media'=>$media[$posts['featured'][$i]->getAttribute('type')]->present('asset'),
                                    'alt'=>$posts['featured'][$i]->present('title')
                                ])
                                @endif
                            </div>
                        @endfor
                    </div>
                    <a class="carousel-control-prev" href="#carousel-home" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">{{trans('js-backend.general.prev')}}</span>
                    </a>
                    <a class="carousel-control-next" href="#carousel-home" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">{{trans('js-backend.general.next')}}</span>
                    </a>
                </div>
            </div>
            @endif
                @if(isset($posts['featured'][3]))
            <div id="blog-featured-right" class="col-lg-5 col-md-12">
                <div class="container p-0">
                    <div class="row">
                        <div class="col-lg-12 featured-content featured-content-top">
                            <div class="right-featured">
                                <div class=right-featured-content">
                                    <a class="fc-cat badge-success"
                                       href="{{route_i18n('blog',$posts['featured'][3]['cat'])}}">{{trans(sprintf('pages.blog.category.%s',$posts['featured'][3]['cat']))}}</a>
                                    <h2 class="fc-title">
                                        <a href="{{route_i18n('blog',$posts['featured'][3]['slug'])}}">{{$posts['featured'][3]->present('title')}}</a>
                                    </h2>
                                </div>
                            </div>
                            @if(isset($media[$posts['featured'][3]->getAttribute('type')]))
                                @include('core::partials.img',[
                                    'media'=>$media[$posts['featured'][3]->getAttribute('type')]->present('asset'),
                                    'alt'=>$posts['featured'][3]->present('title')
                                ])
                            @endif
                        </div>

                        <div class="col-lg-6 col-md-12 featured-content featured-content-bottom">
                            <div class="bottom-featured">
                                <div class=bottom-featured-content">
                                    <a class="fc-cat badge-success"
                                       href="{{route_i18n('blog',$posts['featured'][4]['cat'])}}">{{trans(sprintf('pages.blog.category.%s',$posts['featured'][4]['cat']))}}</a>
                                    <h2 class="fc-title">
                                        <a href="{{route_i18n('blog',$posts['featured'][4]['slug'])}}">{{$posts['featured'][4]->present('title')}}</a>
                                    </h2>
                                </div>
                            </div>
                            @if(isset($media[$posts['featured'][4]->getAttribute('type')]))
                                @include('core::partials.img',[
                                    'media'=>$media[$posts['featured'][4]->getAttribute('type')]->present('asset'),
                                    'alt'=>$posts['featured'][4]->present('title')
                                ])
                            @endif
                        </div>
                        <div class="col-lg-6 col-md-12 featured-content">
                            <div class="bottom-featured">
                                <div class=bottom-featured-content">
                                    <a class="fc-cat badge-success"
                                       href="{{route_i18n('blog',$posts['featured'][5]['cat'])}}">{{trans(sprintf('pages.blog.category.%s',$posts['featured'][5]['cat']))}}</a>
                                    <h2 class="fc-title">
                                        <a href="{{route_i18n('blog',$posts['featured'][5]['slug'])}}">{{$posts['featured'][5]->present('title')}}</a>
                                    </h2>
                                </div>
                            </div>
                            @if(isset($media[$posts['featured'][5]->getAttribute('type')]))
                                @include('core::partials.img',[
                                    'media'=>$media[$posts['featured'][5]->getAttribute('type')]->present('asset'),
                                    'alt'=>$posts['featured'][5]['title']
                                ])
                            @endif
                        </div>
                    </div>
                </div>
            </div>
                @endif
        </div>
    </section>
    <section id="blog-spotlight" class="container">
        <div class="row">
            <div class="col-lg col-lg-8 col-md-12 spotlight-container">
                <div class="container">
                    <ul class="row">
                        @foreach($posts['most_viewed_cat'] as $keyMostViewed =>$mostViewedItems)
                            <li class="col-lg col-lg-6 col-md-12 spotlight-category">
                                <h5 class="bordered">
                                    <span><a href="{{route_i18n('blog.category',$keyMostViewed)}}">{{trans(sprintf('pages.blog.category.%s',$keyMostViewed))}}</a></span>
                                </h5>
                                <ul class="container">
                                    <li class="row headline-post">
                                        <div class="headline-content">
                                            <div class="lfc-title"><a
                                                        href="{{route_i18n('blog',$mostViewedItems[0]['slug'])}}">{{$mostViewedItems[0]['title']}}</a>
                                            </div>
                                            <span class="lfc-date">{{$mostViewedItems[0]->present('date')}}</span>
                                        </div>
                                        @if(isset($media[$mostViewedItems[0]->getAttribute('type')]))
                                            @include('core::partials.img',[
                                                'media'=>$media[$mostViewedItems[0]->getAttribute('type')]->present('asset'),
                                                'alt'=>$mostViewedItems[0]->present('title')
                                            ])
                                        @endif
                                    </li>
                                    @for($i=1;$i<=4;$i++)
                                        <li class="row list-post">
                                            <div class="container">
                                                <div class="row d-flex align-items-center">
                                                    <div class="col-lg-3 col-md-6 list-img-container">
                                                        @if(isset($media[$mostViewedItems[$i]->getAttribute('type')]))
                                                            @include('core::partials.img',[
                                                                'media'=>$media[$mostViewedItems[$i]->getAttribute('type')]->present('asset'),
                                                                'alt'=>$mostViewedItems[$i]->present('title')
                                                            ])
                                                        @endif
                                                    </div>
                                                    <div class="col-lg-9 col-md-6 list-txt-container">
                                                        <div class="row lfc-title"><a
                                                                    href="{{route_i18n('blog',$mostViewedItems[$i]['slug'])}}">{{$mostViewedItems[$i]->present('title')}}</a>
                                                        </div>
                                                        <div class="row lfc-date">{{$mostViewedItems[$i]->present('date')}}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endfor
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <h5 class="bordered"><span>{{trans('titles.follow_us')}}</span></h5>
                            <div class="container p-0 text-center">
                                <div class="row">
                                    <ul class="col social-icon">
                                        <li><a href="{{route('rss',['type'=>'home'])}}" target="_blank"><i class="fa fa-rss"></i></a></li>
                                        <li><a href="#" target="_blank"><i class="fa fa-facebook"></i></a></li>
                                        <li><a href="#" target="_blank"><i class="fa fa-twitter"></i></a></li>
                                        <li><a href="#" target="_blank"><i class="fa fa-instagram"></i></a></li>
                                        <li><a href="#" target="_blank"><i class="fa fa-vimeo"></i></a></li>
                                        <li><a href="#" target="_blank"><i class="fa fa-youtube"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="blog-mvp-container" class="row">
                        <div class="col">
                            <h5 class="bordered"><span>{{trans('pages.blog.most_viewed')}}</span></h5>
                            <div class="container mvp-list">
                                @foreach($posts['most_viewed'] as $mostViewedItems)
                                    <div class="row mvp-list-item d-flex align-items-md-center align-items-lg-start">
                                        <div class="col-lg-3 col-md-6 list-img-container">
                                            @if(isset($media[$mostViewedItems->getAttribute('type')]))
                                                @include('core::partials.img',[
                                                    'media'=>$media[$mostViewedItems->getAttribute('type')]->present('asset'),
                                                    'alt'=>$mostViewedItems->present('title')
                                                ])
                                            @endif
                                        </div>
                                        <div class="col-lg-9 col-md-6 list-txt-container">
                                            <div class="row mli-date">{{$mostViewedItems->present('date')}}</div>
                                            <div class="row mli-title"><a
                                                        href="{{route_i18n('blog',$mostViewedItems['slug'])}}">{{$mostViewedItems->present('title')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

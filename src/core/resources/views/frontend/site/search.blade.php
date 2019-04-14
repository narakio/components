@extends('core::frontend.default')
@section('content')
    <div class="row mt-4">
        <full-page-search initial-value="{{$q}}" search-host-url="{{$search_url}}"
                          rss-root-url="{{route('rss',['type'=>'search'])}}"></full-page-search>
    </div>
@endsection

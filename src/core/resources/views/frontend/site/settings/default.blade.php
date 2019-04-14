@extends('core::frontend.default')

@section('content')
    <div class="row mt-2">
        <div class="col-md-3" style="font-size: 0.9rem;">
            <div class="card">
                <div class="card-header">
                    {{trans('pages.profile.settings_title')}}
                </div>
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action {{($viewName==='profile')?'active':''}}"
                       href="{{route_i18n('profile')}}">{{trans('pages.profile.profile_title')}}</a>
                    <a class="list-group-item list-group-item-action {{($viewName==='account')?'active':''}}"
                       href="{{route_i18n('account')}}">{{trans('pages.profile.account_title')}}</a>
                    <a class="list-group-item list-group-item-action {{($viewName==='notifications')?'active':''}}"
                       href="{{route_i18n('notifications')}}">{{trans('pages.profile.notifications_title')}}</a>
                </div>
            </div>
        </div>
        <div class="col-md-9 pl-0">
            @yield('pane')
        </div>
    </div>
@endsection
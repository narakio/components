@extends('core::frontend.default')

@section('content')
    <div class="container">
        <div class="row error-page-wrapper">
            <div class="col align-self-center">
                <div class="card card-shadow">
                    <div class="container">
                        <div class="row">
                            <div class="col-xl-4 offset-xl-4 fa-container">
                                <span class="fa-stack-1x">
                                    <i class="fa fa-circle fa-stack-1x circle"></i>
                                    <i class="fa fa-exclamation fa-stack-1x fa-inverse exclamation flash"></i>
                                </span>
                            </div>
                        </div>
                        <div class="row code">
                            <div class="col text-center ">
                                <span>@yield('code')</span>
                            </div>
                        </div>
                        <div class="row message">
                            <div class="col text-center">
                                <span>@yield('message')</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

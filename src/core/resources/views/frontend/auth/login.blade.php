@extends('core::frontend.default-bare')
@section('content')
    <div class="container">
        <div class="row justify-content-center mt-5">
            <figure>
                <img src="{{asset(sprintf('media/img/site/%s',env('APP_LOGO_FILENAME')))}}">
            </figure>
        </div>
        @if(is_null($status))
            <div class="row justify-content-md-center mt-5">
                <h3 class="font-light mb-0">{{trans('auth.login_account')}}</h3>
            </div>
            <div id="form-login" class="row justify-content-md-center mt-3">
                @else
                    <div class="row justify-content-md-center mt-3">
                        <div class="col-md-8">
                            <div class="alert alert-{{strpos($status,'error')===false?'success':'danger'}}"
                                 role="alert">
                                <h4 class="alert-heading">{{ trans(sprintf('auth.alerts.%s_title', $status)) }}</h4>
                                <p>{{ trans(sprintf('auth.alerts.%s_body', $status)) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-md-center">
                        @endif
                        <div class="col-md-8">
                            <div class="card card-shadow">
                                <div class="card-body">
                                    <inline-form :class="['form-horizontal','mt-3']"
                                                 :action="'{{ route('login.post') }}'"
                                                 :method="'POST'">
                                        {{ csrf_field() }}
                                        <div class="form-group row">
                                            <label for="email"
                                                   class="col-md-4 col-form-label text-lg-right">{{trans('auth.email_address')}}</label>
                                            <div class="col-md-6">
                                                <input id="email"
                                                       type="email"
                                                       class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                                       name="email"
                                                       value="{{ old('email') }}"
                                                       autocomplete="email"
                                                       required
                                                       autofocus>
                                                @if ($errors->has('email'))
                                                    <div class="invalid-feedback">
                                                        <strong>{{ $errors->first('email') }}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="password"
                                                   class="col-md-4 col-form-label text-lg-right">{{trans('auth.password')}}</label>
                                            <div class="col-md-6">
                                                <input id="password"
                                                       type="password"
                                                       class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                                       name="password"
                                                       autocomplete="current-password"
                                                       required>
                                                @if ($errors->has('password'))
                                                    <div class="invalid-feedback">
                                                        <strong>{{ $errors->first('password') }}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-lg-6 col-md-8 offset-md-2 offset-lg-3 d-flex justify-content-between">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                           class="custom-control-input" id="customCheck1"
                                                           name="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                           for="customCheck1">{{trans('auth.remember_me')}}</label>
                                                </div>
                                                <u>
                                                    <a class="small"
                                                       href="{{ route_i18n('password.request') }}">{{trans('auth.forgot_password')}}</a>
                                                </u>
                                            </div>
                                        </div>
                                        <div class="form-group row mt-2 min-height-2">
                                            <div class="m-auto min-height-2">
                                                <login-o-auth :provider="'google'"></login-o-auth>
                                                <login-o-auth :provider="'twitter'"></login-o-auth>
                                                <login-o-auth :provider="'github'"></login-o-auth>
                                            </div>
                                        </div>
                                        <div class="form-group row mt-4 min-height-2">
                                            <div class="col-xl-8 offset-xl-2 col-lg-6 offset-lg-3 min-height-2">
                                                <submit-button ref="submitButton"
                                                               :block="true"
                                                               :value="'{{trans('auth.login')}}'"></submit-button>
                                            </div>
                                        </div>
                                        <div class="form-group row text-center m-0">
                                            <div class="col align-content-lg-center">
                                                <u>
                                                    <a class="small"
                                                       href="{{ route_i18n('register') }}">{{trans('auth.create_account')}}</a>
                                                </u>
                                            </div>
                                        </div>
                                    </inline-form>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
    </div>
@endsection
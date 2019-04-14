@extends('core::frontend.default-bare')

@section('content')
    <div class="container">
        <div class="row justify-content-center mt-5">
            <img src="{{asset(sprintf('media/img/site/%s',env('APP_LOGO_FILENAME')))}}">
        </div>
        <div class="row justify-content-md-center mt-3">
            <div class="col-md-8">
                    <div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">{{ trans('auth.alerts.email_reset_title') }}</h4>
                        <p>{{ trans('auth.alerts.email_reset_body') }}</p>
                    </div>
            </div>
        </div>
        <div class="row justify-content-md-center mt-3">
            <div class="col-md-8">
                <div class="card card-shadow">
                    <div class="card-body">
                        <form role="form" method="POST" action="{{ route('password.reset.do') }}">
                            {!! csrf_field() !!}
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="form-group row">
                                <label class="col-lg-4 col-form-label text-lg-right">E-Mail Address</label>
                                <div class="col-lg-6">
                                    <input type="email"
                                            class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                            name="email"
                                            value="{{ $email }}">
                                    @if ($errors->has('email'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-4 col-form-label text-lg-right">Password</label>
                                <div class="col-lg-6">
                                    <input type="password"
                                            class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                            name="password">
                                    @if ($errors->has('password'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-4 col-form-label text-lg-right">Confirm Password</label>
                                <div class="col-lg-6">
                                    <input type="password"
                                           class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                                           name="password_confirmation">
                                    @if ($errors->has('password_confirmation'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row pt-3">
                                <div class="col-xl-8 offset-xl-2 col-lg-6 offset-lg-3">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        {{trans('js-backend.pages.auth.reset_password')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

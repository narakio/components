@extends('core::frontend.default-bare')

@section('content')
    <div class="container">
        <div class="row justify-content-center mt-5">
            <img src="{{asset(sprintf('media/img/site/%s',env('APP_LOGO_FILENAME')))}}">
        </div>
        <div class="row justify-content-md-center mt-3">
            <div class="col-md-8">
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @else
                    <div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">{{ trans('auth.alerts.email_title') }}</h4>
                        <p>{{ trans('auth.alerts.email_body') }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="row justify-content-md-center mt-1">
            <div class="col-md-8">
                <div class="card card-shadow">
                    <div class="card-body">
                        <form role="form" method="POST" action="{{ route('password.email') }}">
                            {!! csrf_field() !!}

                            <div class="form-group row">
                                <label class="col-lg-4 col-form-label text-lg-right">{{ trans('auth.content.email') }}</label>

                                <div class="col-lg-6">
                                    <input type="email"
                                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                           name="email" value="{{ old('email') }}">
                                    @if ($errors->has('email'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-lg-6 offset-lg-4">
                                    <button type="submit"
                                            class="btn btn-primary">{{ trans('auth.content.send_link') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

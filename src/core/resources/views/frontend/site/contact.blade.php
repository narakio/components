@extends('core::frontend.default')

@section('content')
    <div id="user-profile" class="container mt-4">
        <div class="col-md-10 offset-md-1">
            <div class="row justify-content-md-center mt-5">
                <h3 class="font-light mb-3">{{trans('pages.contact.form_title')}}</h3>
            </div>
            <div id="user_register_container" class="card card-shadow">
                <div class="card-body">
                    <inline-form :id="'register-form'" :action="'{{ route('contact.send') }}'"
                                 :method="'POST'">
                        {!! csrf_field() !!}
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">{{trans('pages.contact.sender_email')}}</label>
                            <div class="col-lg-6">
                                <input type="email"
                                       class="form-control {{ $errors->has('sender_email') ? ' is-invalid' : '' }}"
                                       name="sender_email"
                                       value="{{ old('sender_email') }}"
                                       maxlength="125"
                                       autocomplete="off"
                                       required>
                                @if ($errors->has('sender_email'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('sender_email') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">{{trans('pages.contact.email_subject')}}</label>
                            <div class="col-lg-6">
                                <input type="text"
                                       class="form-control {{ $errors->has('email_subject') ? ' is-invalid' : '' }}"
                                       name="email_subject"
                                       value="{{ old('email_subject') }}"
                                       maxlength="255"
                                       autocomplete="off"
                                       required>
                                @if ($errors->has('email_subject'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('email_subject') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">{{trans('pages.contact.email_body')}}</label>
                            <div class="col-lg-6">
                                <textarea
                                        class="form-control"
                                        name="email_body"
                                        rows="3"
                                        required></textarea>
                            </div>
                        </div>
                        <div class="form-group row pt-3">
                            <div class="col-xl-6 offset-xl-4 col-lg-6 offset-lg-4">
                                <submit-button
                                        ref="submitButton"
                                        :block="true"
                                        :value="'{{trans('general.send')}}'"></submit-button>
                            </div>
                        </div>
                        <input type="hidden" class="g-recaptcha" name="g-recaptcha" value="">
                    </inline-form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @include('core::frontend.scripts.google-recaptcha',['action'=>'contact'])
@endsection

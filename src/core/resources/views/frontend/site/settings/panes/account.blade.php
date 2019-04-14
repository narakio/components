@extends('core::frontend.site.settings.default')

@section('pane')
    <div id="user-profile" class="container p-0">
        <div class="col p-0">
            <div id="user_register_container" class="card card-shadow">
                <div class="card-body">
                    <inline-form :id="'register-form'" :action="'{{ route('profile.update') }}'"
                                 :method="'POST'">
                        {!! csrf_field() !!}
                        <div class="row form-heading-wrapper">
                            <div class="col-lg-10 offset-lg-1">
                                <h4 class="form-heading">{{trans('pages.profile.new_username_heading')}}</h4>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">
                                <span class="form-has-help"
                                      data-toggle="tooltip"
                                      data-placement="top"
                                      data-original-title="{{
                                      trans('auth.register_username_help')}}">{{
                                      trans('pages.profile.new_username')}}</span>
                            </label>
                            <div class="col-lg-6 validator-wrapper">
                                <input-validator
                                        :name="'username'"
                                        :maxlength="25"
                                        :minlength="5"
                                        :classes="{'form-control':true,'is-invalid':{{
                                            $errors->has('username')?'true':'false'
                                            }} }"
                                        :value="'{{ old('username') }}'"
                                        :validation-type="'username'"
                                        :search-field="'username.exact'"
                                        :errors="'{{$errors->has('username')?$errors->first('username'):''}}'"
                                        search-host-url="{{env('ELASTIC_SEARCH_URL')}}"></input-validator>
                                @if ($errors->has('username'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </div>
                                @else
                                    <small class="form-text text-muted">{{
                                    trans('pages.profile.username_help',[
                                    'username'=>$user->getAttribute('username')
                                    ])}}</small>
                                @endif
                            </div>
                        </div>
                        <div class="row form-heading-wrapper">
                            <div class="col-lg-10 offset-lg-1">
                                <h4 class="form-heading">{{trans('pages.profile.new_password_heading')}}</h4>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">{{
                            trans('pages.profile.current_password') }}</label>
                            <div class="col-lg-6">
                                <input type="password"
                                       class="form-control {{
                                       $errors->has('current_password') ? 'is-invalid' : '' }}"
                                       name="current_password">
                                @if ($errors->has('current_password'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('current_password') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">
                                <span class="form-has-help"
                                      data-toggle="tooltip"
                                      data-placement="top"
                                      data-original-title="{{trans('auth.password_help')}}">{{
                                      trans('pages.profile.new_password')}}</span>
                            </label>
                            <div class="col-lg-6">
                                <password-strength
                                        :has-errors="{{ $errors->has('password') ? 'true' : 'false' }}"
                                        :name="'password'"
                                        :label-hide="'{{trans('auth.hide_password')}}'"
                                        :label-show="'{{trans('auth.show_password')}}'"
                                        :secure-length="8"
                                        :required="false">
                                </password-strength>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label
                                    class="col-lg-4 col-form-label text-lg-right">{{
                                    trans('pages.profile.new_password_confirm') }}</label>
                            <div class="col-lg-6">
                                <input type="password"
                                       class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                       name="password_confirmation">
                                @if ($errors->has('password'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row form-heading-wrapper">
                            <div class="col-lg-10 offset-lg-1">
                                <h4 class="form-heading text-danger">{{trans('pages.profile.account_delete_heading')}}</h4>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-6 offset-md-3 text-center">
                                        <button type="button"
                                                id="btn-account-delete" class="btn btn-outline-danger">{{
                                        trans('pages.profile.account_delete_button')
                                        }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mt-5">
                            <div class="col-xl-8 offset-xl-2 col-lg-6 offset-lg-3">
                                <submit-button
                                        ref="submitButton"
                                        :block="true"
                                        :value="'{{trans('js-backend.general.save')}}'"></submit-button>
                            </div>
                        </div>
                    </inline-form>
                </div>
            </div>
        </div>
    </div>
@endsection

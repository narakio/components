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
                                <h4 class="form-heading">{{trans('pages.profile.new_email_heading')}}</h4>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">
                                <span>{{trans('pages.profile.new_email_address')}}</span>
                            </label>
                            <div class="col-lg-6 validator-wrapper">
                                <input-validator
                                        :type="'email'"
                                        :name="'email'"
                                        :validation-type="'email'"
                                        :maxlength="25"
                                        :classes="{'form-control':true,'is-invalid':{{
                                            $errors->has('email')?'true':'false'
                                            }} }"
                                        :value="'{{ old('email') }}'"
                                        :search-field="'email'"
                                        search-host-url="{{env('ELASTIC_SEARCH_URL')}}"
                                        :errors="'{{
                                            $errors->has('email')?$errors->first('email'):''
                                            }}'"></input-validator>
                                @if ($errors->has('email'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </div>
                                @else
                                    <small
                                            class="form-text text-muted">{{trans('pages.profile.email_help',['email'=>$user->getAttribute('email')])}}</small>
                                @endif
                            </div>
                        </div>
                        <div class="row form-heading-wrapper">
                            <div class="col-lg-10 offset-lg-1">
                                <h4 class="form-heading">{{trans('pages.profile.notifications_heading')}}</h4>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-10 offset-lg-1">
                                        <p class="font-light">{{trans('pages.profile.notifications_help')}}</p>
                                    </div>
                                </div>
                                @foreach($mailing_lists as $id=>$list)
                                <div class="row">
                                    <div class="col-lg-6 offset-lg-4">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   class="custom-control-input"
                                                   id="notif-chk-{{$id}}"
                                                   name="notifications[{{$id}}]" {{isset($subscribed[$id])?'checked':''}}>
                                            <label class="custom-control-label"
                                                   for="notif-chk-{{$id}}">{{trans(sprintf('general.mailing_lists.%s',$list))}}</label>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group row mt-5">
                            <div class="col-xl-8 offset-xl-2 col-lg-6 offset-lg-3">
                                <submit-button
                                        ref="submitButton"
                                        :block="true" :value="'{{trans('js-backend.general.save')}}'"></submit-button>
                            </div>
                        </div>
                    </inline-form>
                </div>
            </div>
        </div>
    </div>
@endsection

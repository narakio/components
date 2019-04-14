@extends('core::frontend.site.settings.default')

@section('pane')
    <div id="user-profile" class="container p-0">
        <div class="col p-0">
            <div id="user_register_container" class="card card-shadow">
                <div class="card-body">
                    <inline-form :id="'register-form'" :action="'{{ route('profile.update') }}'"
                                 :method="'POST'">
                        {!! csrf_field() !!}
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">{{trans('js-backend.db.first_name')}}</label>
                            <div class="col-lg-6">
                                <input type="text"
                                       class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}"
                                       name="first_name"
                                       value="{{ old('first_name',$user->getAttribute('first_name')) }}"
                                       maxlength="75"
                                       autocomplete="given-name">
                                @if ($errors->has('first_name'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('first_name') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">{{trans('js-backend.db.last_name')}}</label>
                            <div class="col-lg-6">
                                <input type="text"
                                       class="form-control{{ $errors->has('last_name',$user->getAttribute('last_name')) ? ' is-invalid' : '' }}"
                                       name="last_name"
                                       value="{{ old('last_name',$user->getAttribute('last_name')) }}"
                                       maxlength="75"
                                       autocomplete="family-name">
                                @if ($errors->has('last_name'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row pt-5">
                            <div class="col-xl-8 offset-xl-2 col-lg-6 offset-lg-3">
                                <submit-button
                                        ref="submitButton"
                                        :block="true" :value="'{{trans('js-backend.general.save')}}'"></submit-button>
                            </div>
                        </div>
                        <avatar-uploader
                                ref="avatarUploader"
                                :user="{{json_encode(auth()->user()->only('username'))}}"
                                :avatars-parent="{{json_encode($avatars)}}"
                        :extra-headers="{{json_encode(
                        ['Authorization'=>sprintf('Bearer %s',\Session::get('jwt_token'))]
                        )}}"></avatar-uploader>
                    </inline-form>
                </div>
            </div>
        </div>
    </div>
@endsection

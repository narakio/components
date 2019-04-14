<?php namespace Naraki\Sentry\Requests\Frontend;

use Naraki\Core\Support\Requests\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UpdateUser extends FormRequest
{
    public function rules()
    {
        return [
            'first_name' => 'max:75',
            'last_name' => 'max:75',
            'username' => 'nullable|regex:/^\w+$/|min:5|max:25|unique:users',
            'email' => 'nullable|email|unique:people',
            'password' => 'confirmed|same_password',
            'current_password' => 'current_password',
            'notifications' => 'nullable'
        ];
    }

    public function prepareForValidation()
    {
        Validator::extend('same_password', function ($attribute, $value, $parameters, $validator) {
            if (Hash::check(
                app('request')->get('password'),
                auth()->user()->getAttribute('password')
            )) {
                $validator->errors()->add('password', trans('error.form.identical_passwords'));
                return false;
            }
            return true;
        });
        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            if (!Hash::check(
                app('request')->get('current_password'),
                auth()->user()->getAttribute('password')
            )) {
                $validator->errors()->add('current_password', trans('error.form.wrong_password'));
                return false;
            }
            return true;
        });
        parent::prepareForValidation();
    }

    public function afterValidation()
    {
        $input = $this->input();
        $keptInput = [];
        foreach ($input as $k => $v) {
            if (isset($v) && !empty($v)) {
                $keptInput[$k] = $v;
            }
        }
        $this->replace($keptInput);
    }

}

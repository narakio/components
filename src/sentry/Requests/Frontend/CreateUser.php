<?php namespace Naraki\Sentry\Requests\Frontend;

use Naraki\Sentry\Contracts\Person;
use Naraki\Core\Support\Requests\FormRequest;
use Illuminate\Support\Facades\Validator;

class CreateUser extends FormRequest
{
    public function rules()
    {
        return [
            'first_name' => 'max:75',
            'last_name' => 'max:75',
            'username' => 'required|string|min:5|max:25|regex:/^\w+$/|unique:users',
            'email' => 'required|uniq|email|max:255',
            'password' => 'required|min:8|confirmed',
            'stat_user_timezone' => 'nullable',
            'g-recaptcha' => 'captcha'
        ];
    }

    public function afterValidation()
    {
        $input = $this->input();

        unset($input['password_confirmation'], $input['g-recaptcha']);
        $this->replace($input);
    }

    public function prepareForValidation()
    {
        Validator::extend('uniq', function ($attribute, $value, $parameters, $validator) {
            $user = app(Person::class)->select('user_id')->where('email', $value)->first();
            if (!is_null($user) && intval($user->getAttribute('user_id')) > 0) {
                $validator->errors()->add('email', trans('validation.unique', ['attribute' => 'email']));
                return false;
            }
            return true;
        });
        Validator::extend('captcha', [\Naraki\Core\Support\Vendor\GoogleRecaptcha::class, 'validate']);
        parent::prepareForValidation();
    }
}

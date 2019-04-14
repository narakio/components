<?php namespace Naraki\Mail\Support\Requests;

use Naraki\Core\Support\Requests\FormRequest;
use Illuminate\Support\Facades\Validator;

class SendContactEmail extends FormRequest
{
    public function rules()
    {
        return [
            'sender_email' => 'nullable|email',
            'email_subject' => 'nullable|max:255',
            'email_body' => 'nullable',
            'g-recaptcha' => 'captcha'
        ];
    }

    public function filters()
    {
        return [
            'email_subject' => 'strip_tags',
            'sender_email' => 'strip_tags',
            'email_body' => 'purify'
        ];
    }

    public function afterValidation()
    {
        $input = $this->input();
        unset($input['g-recaptcha']);
        $this->replace($input);
    }

    public function prepareForValidation()
    {
        Validator::extend('captcha', [\Naraki\Core\Support\Vendor\GoogleRecaptcha::class, 'validate']);
        parent::prepareForValidation();
    }
}

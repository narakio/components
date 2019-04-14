<?php

namespace Naraki\Core\Support\Requests;

use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;

class FormRequest extends LaravelFormRequest
{
    protected $activateTagStrippingFilter = true;

    /**
     *  Sanitize this request's input
     *
     * @return void
     */
    public function sanitize()
    {
        $this->sanitizer = new Sanitizer($this->input(), $this->filters(), $this->activateTagStrippingFilter);

        $this->replace($this->sanitizer->sanitize());
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Sanitizing inputs before validation takes place
     *
     * @see \Illuminate\Validation\ValidatesWhenResolvedTrait::validateResolved
     */
    public function prepareForValidation()
    {
        $this->sanitize();
    }

    /**
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    public function withValidator($validator)
    {
        if (method_exists($this, 'afterValidation')) {
            $validator->after([$this, 'afterValidation']);
        }
    }

    public function filters()
    {
        return [];
    }

    public function rules()
    {
        return [];
    }
}

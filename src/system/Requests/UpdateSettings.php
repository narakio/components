<?php namespace Naraki\System\Requests;

use Naraki\Core\Support\Requests\FormRequest;

class UpdateSettings extends FormRequest
{
    public function afterValidation()
    {
        $input = $this->input();
        $input['jsonld'] = !isset($input['jsonld']) ? false : $input['jsonld']=="true";
        $input['robots'] = !isset($input['robots']) ? false : $input['robots']=="true";

        $this->replace($input);
    }

}
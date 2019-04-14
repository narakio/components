<?php namespace Naraki\System\Requests;

use Naraki\Core\Support\Requests\FormRequest;

class UpdateSitemapSettings extends FormRequest
{
    public function afterValidation()
    {
        $input = $this->input();
        $input['sitemap'] = !isset($input['sitemap']) ? false : $input['sitemap']=="true";

        $this->replace($input);
    }

}
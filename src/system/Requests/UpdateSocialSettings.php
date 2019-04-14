<?php namespace Naraki\System\Requests;

use Naraki\Core\Support\Requests\FormRequest;

class UpdateSocialSettings extends FormRequest
{
    public function afterValidation()
    {
        $input = $this->input();
        $input['open_graph'] = !isset($input['open_graph']) ? false : $input['open_graph']=="true";
        $input['twitter_cards'] = !isset($input['twitter_cards']) ? false : $input['twitter_cards']=="true";

        $this->replace($input);
    }

}
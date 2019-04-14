<?php namespace Naraki\Sentry\Requests\Admin;

use Naraki\Core\Support\Requests\FormRequest;
use Naraki\Permission\Traits\ProcessesPermissions;

class CreateGroup extends FormRequest
{
    use ProcessesPermissions;

    public function rules()
    {
        return [
            'group_name' => 'max:60|unique:groups,group_name',
            'group_mask' => 'required|integer|min:100',
        ];
    }

    public function afterValidation()
    {
        $input = $this->input();
        $this->processPermissions($input['permissions']);

        unset($input['new_group_name'], $input['permissions']);
        $this->replace($input);
    }
}
